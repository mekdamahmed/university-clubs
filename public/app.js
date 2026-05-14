const API_URL = 'http://127.0.0.1:8000/api/v1';
let currentUser = null;

document.addEventListener("DOMContentLoaded", () => {
    const token = localStorage.getItem('token');
    if (token) { currentUser = JSON.parse(localStorage.getItem('user')); showDashboard(); }
});

function toggleAuth() {
    document.getElementById('login-section').classList.toggle('hidden');
    document.getElementById('register-section').classList.toggle('hidden');
}

async function apiCall(endpoint, method = 'GET', body = null) {
    const token = localStorage.getItem('token');
    const options = {
        method: method,
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', ...(token && { 'Authorization': `Bearer ${token}` }) }
    };
    if (body) options.body = JSON.stringify(body);
    try {
        const response = await fetch(`${API_URL}${endpoint}`, options);
        return { status: response.status, data: await response.json() };
    } catch (e) { return { status: 500, data: { message: "Server Error" } }; }
}

document.getElementById('register-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const res = await apiCall('/register', 'POST', { name: document.getElementById('reg-name').value, email: document.getElementById('reg-email').value, password: document.getElementById('reg-password').value });
    if (res.status === 201 || res.status === 200) { alert('Registered!'); document.getElementById('register-form').reset(); toggleAuth(); } 
    else { alert('Error: ' + res.data.message); }
});

document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const res = await apiCall('/login', 'POST', { email: document.getElementById('login-email').value, password: document.getElementById('login-password').value });
    if (res.status === 200) { localStorage.setItem('token', res.data.data.token); localStorage.setItem('user', JSON.stringify(res.data.data.user)); location.reload(); } 
    else { alert('Error: Invalid Credentials'); }
});

async function logout() { await apiCall('/logout', 'POST'); localStorage.clear(); location.reload(); }

// Router
function showDashboard() {
    document.getElementById('auth-area').classList.add('hidden');
    document.getElementById('dashboard-area').classList.remove('hidden');
    document.getElementById('user-greeting').innerText = `Welcome, ${currentUser.name}`;

    loadPublicClubs(); loadEvents(); loadMyTasks(); loadAnnouncements(); loadMyClubs();

    if (currentUser.is_admin) {
        document.getElementById('admin-view').classList.remove('hidden');
        loadAdminStats(); loadAdminClubs();
        document.getElementById('leader-select').innerHTML = `<option value="${currentUser.id}">${currentUser.name} (Me)</option>`;
    }
}

// Announcements & Admin
async function loadAnnouncements() {
    const res = await apiCall('/announcements');
    if (res.status === 200) {
        const list = document.getElementById('announcements-list'); list.innerHTML = '';
        res.data.data.forEach(ann => { list.innerHTML += `<div class="announcement-card"><h4>${ann.title} <small>(${ann.club.name})</small></h4><p>${ann.content}</p><small>By: ${ann.author.name}</small></div>`; });
    }
}
document.getElementById('create-announcement-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const res = await apiCall('/announcements', 'POST', { club_id: document.getElementById('current-managing-club').value, title: document.getElementById('ann-title').value, content: document.getElementById('ann-content').value });
    alert(res.data.message); document.getElementById('create-announcement-form').reset(); loadAnnouncements();
});
async function loadAdminStats() {
    const res = await apiCall('/admin/stats');
    if (res.status === 200) {
        document.getElementById('admin-stats').innerHTML = `<div class="stat-box"><h3>${res.data.data.total_clubs}</h3><p>Clubs</p></div><div class="stat-box"><h3>${res.data.data.active_members}</h3><p>Active Members</p></div><div class="stat-box"><h3>${res.data.data.total_events}</h3><p>Events</p></div>`;
    }
}
async function loadAdminClubs() {
    const res = await apiCall('/admin/clubs/all');
    if (res.status === 200) {
        const tbody = document.getElementById('admin-clubs-list'); tbody.innerHTML = '';
        res.data.data.forEach(club => {
            const isDeleted = club.deleted_at !== null;
            const actionBtn = isDeleted 
                ? `<button onclick="restoreClub(${club.id})" class="btn btn-warning btn-sm">Restore</button>` 
                : `<button onclick="deleteClub(${club.id})" class="btn btn-danger btn-sm">Delete</button>`;
            
            tbody.innerHTML += `<tr style="${isDeleted ? 'background-color: #ffe6e6;' : ''}">
                <td>${club.name}</td>
                <td><input type="number" id="change-leader-${club.id}" value="${club.leader_id}" style="width:50px;">
                <button onclick="changeLeader(${club.id})" class="btn btn-warning btn-sm">Change</button></td>
                <td>${actionBtn} ${!isDeleted ? `<button onclick="openManageClub(${club.id}, '${club.name}')" class="btn btn-success btn-sm">Enter</button>` : ''}</td>
            </tr>`;
        });
    }
}

async function deleteClub(id) {
    if(confirm('Are you sure you want to delete this club?')) {
        await apiCall(`/admin/clubs/${id}`, 'DELETE');
        loadAdminClubs(); loadPublicClubs();
    }
}

async function restoreClub(id) {
    await apiCall(`/admin/clubs/${id}/restore`, 'POST');
    loadAdminClubs(); loadPublicClubs();
}
document.getElementById('create-club-form').addEventListener('submit', async (e) => {
    e.preventDefault(); await apiCall('/admin/clubs', 'POST', { name: document.getElementById('new-club-name').value, description: document.getElementById('new-club-desc').value, leader_id: document.getElementById('leader-select').value });
    document.getElementById('create-club-form').reset(); loadAdminClubs(); loadPublicClubs();
});
async function changeLeader(clubId) { await apiCall(`/admin/clubs/${clubId}/leader`, 'PUT', { leader_id: document.getElementById(`change-leader-${clubId}`).value }); loadAdminClubs(); loadPublicClubs(); }

// Clubs
async function loadPublicClubs() {
    const res = await apiCall('/clubs');
    if (res.status === 200) {
        const grid = document.getElementById('public-clubs-list'); grid.innerHTML = '';
        let userLeadsAClub = false; 
        res.data.data.forEach(club => {
            let actionHtml = '';
            if (club.user_status === 'leader') {
                actionHtml = `<span class="badge bg-leader">You lead this club</span>`;
                if (!userLeadsAClub && !currentUser.is_admin) { openManageClub(club.id, club.name); userLeadsAClub = true; }
                actionHtml += `<br><button onclick="openManageClub(${club.id}, '${club.name}')" class="btn btn-warning btn-sm mt-10" style="width:100%;">⚙️ Manage This Club</button>`;
            } else if (club.user_status === 'approved') { actionHtml = `<span class="badge bg-approved">Active Member</span>`; } 
            else if (club.user_status === 'pending') { actionHtml = `<span class="badge bg-pending">Pending...</span>`; } 
            else { actionHtml = `<button onclick="apiCall('/clubs/${club.id}/apply', 'POST').then(()=>{alert('Sent!');loadPublicClubs();})" class="btn btn-primary btn-sm mt-10">Request to Join</button>`; }
            
            grid.innerHTML += `<div class="card"><h4>${club.name}</h4><p style="font-size:12px; color:gray;">Leader: ${club.leader ? club.leader.name : 'Unknown'}</p><p>${club.description}</p>${actionHtml}</div>`;
        });
    }
}

// Leader Dashboard 
function openManageClub(clubId, clubName) {
    document.getElementById('leader-view').classList.remove('hidden');
    document.getElementById('manage-club-title').innerText = `⭐ Managing: ${clubName}`;
    document.getElementById('current-managing-club').value = clubId;
    loadLeaderMembers(clubId); loadClubTasks(clubId); loadLeaderEvents(clubId);
}

async function loadLeaderMembers(clubId) {
    const res = await apiCall(`/clubs/${clubId}/members`);
    if (res.status === 200) {
        const pendingTbody = document.getElementById('pending-requests-list');
        const approvedTbody = document.getElementById('approved-members-list');
        const taskSelect = document.getElementById('task-member-select');
        
        pendingTbody.innerHTML = ''; 
        approvedTbody.innerHTML = ''; 
        taskSelect.innerHTML = '<option value="">-- Select Member --</option>';

        res.data.data.pending_requests.forEach(u => {
            pendingTbody.innerHTML += `<tr>
                <td><strong>ID: ${u.id}</strong></td>
                <td>${u.name}</td>
                <td>
                    <button onclick="manageAction('/clubs/${clubId}/members/${u.id}/accept')" class="btn btn-success btn-sm">Accept</button> 
                    <button onclick="manageAction('/clubs/${clubId}/members/${u.id}/kick')" class="btn btn-danger btn-sm">Reject</button>
                </td>
            </tr>`;
        });

        const leader = res.data.data.leader;
        if (leader) {
            approvedTbody.innerHTML += `<tr style="background-color: #fcf3ff;">
                <td style="color:#8e44ad;"><strong>ID: ${leader.id}</strong></td>
                <td><b>${leader.name}</b> <span class="badge bg-leader">👑 Leader</span></td>
                <td>${leader.email}</td>
                <td style="color:green; font-weight:bold;">${leader.completed_tasks || 0}</td>
                <td style="color:red; font-weight:bold;">${leader.absences || 0}</td>
                <td><span style="color:gray; font-size:12px; font-style:italic;">Leader (Cannot Kick)</span></td>
            </tr>`;
            taskSelect.innerHTML += `<option value="${leader.id}">${leader.name} (Leader)</option>`;
        }

        res.data.data.approved_members.forEach(u => {
            if (leader && u.id === leader.id) return;

            approvedTbody.innerHTML += `<tr>
                <td style="color:#2980b9;"><strong>ID: ${u.id}</strong></td>
                <td><b>${u.name}</b></td>
                <td>${u.email}</td>
                <td style="color:green; font-weight:bold;">${u.completed_tasks}</td>
                <td style="color:red; font-weight:bold;">${u.absences}</td>
                <td><button onclick="manageAction('/clubs/${clubId}/members/${u.id}/kick')" class="btn btn-danger btn-sm">Kick</button></td>
            </tr>`;
            taskSelect.innerHTML += `<option value="${u.id}">${u.name}</option>`;
        });
    }
}
async function manageAction(endpoint) { await apiCall(endpoint, 'POST'); loadLeaderMembers(document.getElementById('current-managing-club').value); }

// Leader Tasks View
async function loadClubTasks(clubId) {
    const res = await apiCall(`/clubs/${clubId}/tasks`);
    if (res.status === 200) {
        const tbody = document.getElementById('leader-tasks-list'); tbody.innerHTML = '';
        res.data.data.forEach(task => {
            let badge = task.status_label === 'Completed' ? '<span class="badge bg-approved">Completed</span>' : 
                        (task.status_label === 'Failed' ? '<span class="badge bg-failed">Failed</span>' : '<span class="badge bg-pending">Pending</span>');
            tbody.innerHTML += `<tr><td>${task.title}</td><td>${task.assignee.name}</td><td>${task.due_date}</td><td>${badge}</td></tr>`;
        });
    }
}

document.getElementById('assign-task-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    await apiCall('/tasks', 'POST', { 
        club_id: document.getElementById('current-managing-club').value, 
        assigned_to: document.getElementById('task-member-select').value,
        title: document.getElementById('task-title').value, 
        description: 'Assigned Task',
        due_date: document.getElementById('task-due-date').value
    });
    document.getElementById('assign-task-form').reset(); alert('Task Assigned'); loadClubTasks(document.getElementById('current-managing-club').value);
});

// Member Tasks View
async function loadMyTasks() {
    const res = await apiCall('/my-tasks');
    if (res.status === 200 && res.data.data.length > 0) {
        document.getElementById('my-tasks-panel').classList.remove('hidden');
        const tbody = document.getElementById('my-tasks-list'); tbody.innerHTML = '';
        res.data.data.forEach(task => {
            let badge = task.status_label === 'Completed' ? '<span class="badge bg-approved">Completed</span>' : 
                        (task.status_label === 'Failed' ? '<span class="badge bg-failed">Failed</span>' : '<span class="badge bg-pending">Pending</span>');
            let action = task.status_label === 'Pending' ? `<button onclick="completeTask(${task.id})" class="btn btn-success btn-sm">Mark Done</button>` : '';
            tbody.innerHTML += `<tr><td>${task.title}</td><td><b>${task.club.name}</b></td><td>${task.due_date}</td><td>${badge}</td><td>${action}</td></tr>`;
        });
    } else {
        document.getElementById('my-tasks-panel').classList.add('hidden');
    }
}
async function completeTask(id) {
    const res = await apiCall(`/tasks/${id}/complete`, 'POST');
    if(res.status === 200) { loadMyTasks(); } else { alert(res.data.message); }
}

// Events & Attendance
document.getElementById('create-event-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    await apiCall('/events', 'POST', { club_id: document.getElementById('current-managing-club').value, title: document.getElementById('event-title').value, description: 'Event', event_date: document.getElementById('event-date').value, location: document.getElementById('event-location').value });
    document.getElementById('create-event-form').reset(); loadEvents(); loadLeaderEvents(document.getElementById('current-managing-club').value);
});

async function loadEvents() {
    const res = await apiCall('/events/upcoming');
    if(res.status === 200) {
        const ul = document.getElementById('upcoming-events-list'); ul.innerHTML = '';
        const mClub = document.getElementById('current-managing-club') ? document.getElementById('current-managing-club').value : null;
        res.data.data.forEach(e => {
            let delBtn = (currentUser && (currentUser.is_admin || e.club_id == mClub)) ? `<button onclick="deleteEvent(${e.id})" class="btn btn-danger btn-sm" style="float:right;">Delete</button>` : '';
            ul.innerHTML += `<li style="padding:10px; border-bottom:1px solid #eee;"><b>${e.title}</b> (${e.club.name}) ${delBtn}<br><small>📍 ${e.location} | 📅 ${e.event_date}</small></li>`;
        });
    }
}
async function deleteEvent(id) { if(confirm('Delete event?')) { await apiCall(`/events/${id}`, 'DELETE'); loadEvents(); loadLeaderEvents(document.getElementById('current-managing-club').value); } }

async function loadLeaderEvents(clubId) {
    const res = await apiCall(`/events/archived?club_id=${clubId}`);
    if(res.status === 200) {
        const ul = document.getElementById('archived-events-list'); ul.innerHTML = '';
        res.data.data.forEach(e => {
            ul.innerHTML += `<li style="background:#f9f9f9; padding:10px; margin-bottom:5px; border-left:4px solid #f39c12;"><b>${e.title}</b><br><small>📍 ${e.location} | 📅 ${e.event_date}</small><button onclick="openAttendance(${e.id})" class="btn btn-warning btn-sm" style="float:right; margin-top:-20px;">Manage Attendance</button></li>`;
        });
    }
}

async function openAttendance(eventId) {
    document.getElementById('attendance-modal').style.display = 'block';
    document.getElementById('att-event-id').value = eventId;
    const res = await apiCall(`/events/${eventId}/attendance`);
    if(res.status === 200) {
        const tbody = document.getElementById('attendance-list'); tbody.innerHTML = '';
        res.data.data.forEach(member => {
            let statusBadge = member.attendance_status === 'present' ? '<span class="badge bg-approved">Present</span>' : 
                             (member.attendance_status === 'absent' ? '<span class="badge bg-failed">Absent</span>' : '<span class="badge bg-pending">Unrecorded</span>');
            
            let btnClassP = member.attendance_status === 'present' ? 'att-present' : '';
            let btnClassA = member.attendance_status === 'absent' ? 'att-absent' : '';

            tbody.innerHTML += `
                <tr>
                    <td><b>${member.name}</b></td>
                    <td>${member.email}</td>
                    <td>${statusBadge}</td>
                    <td>
                        <button class="att-btn ${btnClassP}" onclick="markAttendance(${eventId}, ${member.id}, 'present')">✔️ Present</button>
                        <button class="att-btn ${btnClassA}" onclick="markAttendance(${eventId}, ${member.id}, 'absent')">❌ Absent</button>
                    </td>
                </tr>
            `;
        });
    }
}
async function markAttendance(eventId, userId, status) {
    await apiCall(`/events/${eventId}/attendance`, 'POST', {user_id: userId, status: status});
    openAttendance(eventId); 
    loadLeaderMembers(document.getElementById('current-managing-club').value); 
}


async function loadMyClubs() {
    const res = await apiCall('/my-clubs');
    if (res.status === 200 && res.data.data.length > 0) {
        document.getElementById('my-clubs-panel').classList.remove('hidden');
        const list = document.getElementById('my-joined-clubs-list');
        list.innerHTML = '';
        res.data.data.forEach(club => {
            let joinDate = club.pivot && club.pivot.joined_at ? club.pivot.joined_at.split('T')[0] : 'Unknown';
            let leaderBadge = club.is_leader_badge ? `<span class="badge bg-leader" style="float:right;">👑 Leader</span>` : '';
            list.innerHTML += `
                <div class="card" style="border: 1px solid #27ae60;">
                    <h4 style="color: #27ae60;">${club.name} ${leaderBadge}</h4>
                    <p style="font-size: 13px; color: #555;"><strong>Joined:</strong> ${joinDate}</p>
                    <p>${club.description}</p>
                </div>
            `;
        });
    }
}