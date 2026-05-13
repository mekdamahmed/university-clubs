<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enterprise Club System</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
    <style>
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 12px; font-weight: bold; color: white;}
        .bg-pending { background-color: #f39c12; }
        .bg-approved { background-color: #27ae60; }
        .bg-leader { background-color: #8e44ad; }
        .bg-failed { background-color: #e74c3c; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); }
        .modal-content { background: white; width: 60%; margin: 5% auto; padding: 25px; border-radius: 8px; max-height: 80vh; overflow-y: auto;}
        .hidden { display: none !important; }
        .announcement-card { background: #e8f4f8; border-left: 4px solid #3498db; padding: 15px; margin-bottom: 10px; border-radius: 4px;}
        .att-btn { padding: 6px 12px; border: 1px solid #ccc; cursor: pointer; background: #eee; border-radius: 4px; font-weight: bold;}
        .att-present { background: #2ecc71; color: white; border-color: #27ae60; }
        .att-absent { background: #e74c3c; color: white; border-color: #c0392b; }
    </style>
</head>
<body>

    <div id="auth-area" class="auth-wrapper">
        <div class="auth-box">
            <h2 class="text-center">University Portal</h2><hr>
            <div id="login-section">
                <form id="login-form">
                    <div class="form-group"><label>Email</label><input type="email" id="login-email" required></div>
                    <div class="form-group"><label>Password</label><input type="password" id="login-password" required></div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <p class="text-center mt-10">New? <a href="#" onclick="toggleAuth()">Register</a></p>
            </div>
            <div id="register-section" class="hidden">
                <form id="register-form">
                    <div class="form-group"><label>Name</label><input type="text" id="reg-name" required></div>
                    <div class="form-group"><label>Email</label><input type="email" id="reg-email" required></div>
                    <div class="form-group"><label>Password</label><input type="password" id="reg-password" minlength="6" required></div>
                    <button type="submit" class="btn btn-success w-100">Register</button>
                </form>
                <p class="text-center mt-10">Have account? <a href="#" onclick="toggleAuth()">Login</a></p>
            </div>
        </div>
    </div>

    <div id="dashboard-area" class="hidden">
        <nav class="top-nav">
            <div class="nav-brand">Clubs Portal</div>
            <div class="nav-user"><span id="user-greeting"></span> <button onclick="logout()" class="btn btn-danger btn-sm">Logout</button></div>
        </nav>

        <div class="dashboard-container">
            
            <!-- Admin View -->
            <div id="admin-view" class="role-section hidden">
                <h2 class="section-title">👑 Admin Control Center</h2>
                <div class="stats-container" id="admin-stats"></div>
                <div class="panel">
                    <h3>All System Clubs</h3>
                    <form id="create-club-form" class="inline-form">
                        <input type="text" id="new-club-name" placeholder="Club Name" required>
                        <input type="text" id="new-club-desc" placeholder="Description" required>
                        <select id="leader-select" required><option value="">-- Assign Leader --</option></select>
                        <button type="submit" class="btn btn-primary">Create Club</button>
                    </form>
                    <table class="data-table mt-10">
                        <thead><tr><th>Name</th><th>Leader</th><th>Actions</th></tr></thead>
                        <tbody id="admin-clubs-list"></tbody>
                    </table>
                </div>
            </div>

            <!-- Leader View -->
            <div id="leader-view" class="role-section hidden" style="border: 2px solid #8e44ad; padding: 15px; border-radius: 8px; background: #fafafa;">
                <h2 class="section-title" id="manage-club-title" style="color: #8e44ad;">⭐ Managing Club</h2>
                <input type="hidden" id="current-managing-club">
                
                <div class="panel">
                    <h3>⏳ Pending Requests</h3>
                    <table class="data-table">
                        <thead><tr><th>Name</th><th>Action</th></tr></thead>
                        <tbody id="pending-requests-list"></tbody>
                    </table>
                    <hr>
                    <h3>✅ Active Members Details</h3>
                    <table class="data-table">
                        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Tasks Done</th><th>Absences</th><th>Action</th></tr></thead>
                        <tbody id="approved-members-list"></tbody>
                    </table>
                </div>

                <div class="grid-2 mt-10">
                    <div class="panel">
                        <h3>📝 Assign & Track Tasks</h3>
                        <form id="assign-task-form" class="inline-form">
                            <select id="task-member-select" required></select>
                            <input type="text" id="task-title" placeholder="Task Title" required style="flex:1;">
                            <input type="date" id="task-due-date" required title="Deadline">
                            <button type="submit" class="btn btn-success">Assign</button>
                        </form>
                        <table class="data-table mt-10">
                            <thead><tr><th>Task</th><th>Assigned To</th><th>Deadline</th><th>Status</th></tr></thead>
                            <tbody id="leader-tasks-list"></tbody>
                        </table>
                    </div>
                    
                    <div class="panel">
                        <h3>📢 Post Announcement</h3>
                        <form id="create-announcement-form" class="inline-form">
                            <input type="text" id="ann-title" placeholder="Title" required>
                            <input type="text" id="ann-content" placeholder="Content/Details" required style="width: 100%;">
                            <button type="submit" class="btn btn-primary mt-10">Post</button>
                        </form>
                        <hr>
                        <h3>📅 Create Event</h3>
                        <form id="create-event-form" class="inline-form">
                            <input type="text" id="event-title" placeholder="Event Name" required>
                            <input type="date" id="event-date" required>
                            <input type="text" id="event-location" placeholder="Location" required>
                            <button type="submit" class="btn btn-primary">Add Event</button>
                        </form>
                    </div>
                </div>

                <div class="panel mt-10">
                    <h3>🗄️ Past Events & Attendance Records</h3>
                    <ul id="archived-events-list" class="simple-list"></ul>
                </div>
            </div>

            <!-- Member View -->
            <div id="member-view" class="role-section">
                <h2 class="section-title">Student Area</h2>
                <!-- New Feature: Show Student's Approved Clubs -->
                <div class="panel hidden" id="my-clubs-panel" style="border-left: 4px solid #27ae60; background: #f4fdf8;">
                    <h3 style="color: #27ae60;">🎓 My Official Memberships</h3>
                    <div id="my-joined-clubs-list" class="cards-grid"></div>
                </div>
                <div class="panel hidden" id="my-tasks-panel">
                    <h3>My Assigned Tasks</h3>
                    <table class="data-table">
                        <thead><tr><th>Task</th><th>Club</th><th>Deadline</th><th>Status</th></tr></thead>
                        <tbody id="my-tasks-list"></tbody>
                    </table>
                </div>

                <div class="grid-2">
                    <div class="panel">
                        <h3>📢 Club Announcements</h3>
                        <div id="announcements-list"></div>
                    </div>
                    <div class="panel">
                        <h3>📅 Upcoming Events</h3>
                        <ul id="upcoming-events-list" class="simple-list"></ul>
                    </div>
                </div>

                <div class="panel mt-10">
                    <h3>Explore Clubs</h3>
                    <div id="public-clubs-list" class="cards-grid"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Modal (Organized UI) -->
    <div id="attendance-modal" class="modal">
        <div class="modal-content">
            <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px;">📋 Attendance Report</h3>
            <input type="hidden" id="att-event-id">
            <table class="data-table mt-10">
                <thead><tr><th>Member Name</th><th>Email</th><th>Status</th><th>Record Action</th></tr></thead>
                <tbody id="attendance-list"></tbody>
            </table>
            <div style="text-align: right; margin-top: 15px;">
                <button onclick="document.getElementById('attendance-modal').style.display='none'" class="btn btn-primary">Done / Close</button>
            </div>
        </div>
    </div>

    <script src="{{ asset('app.js') }}?v=1.3"></script>
</body>
</html>