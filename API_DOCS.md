# RESTful API Documentation


> **🔐 Authentication:** All endpoints (except Register/Login) require a Bearer Token provided by Laravel Sanctum. Include `Authorization: Bearer <your_token>` in the request header.

---

### 1. Authentication (`/api/v1/auth`)
| Method | Endpoint | Description | Body Parameters |
| :--- | :--- | :--- | :--- |
| `POST` | `/register` | Register a new user | `name`, `email`, `password`, `password_confirmation` |
| `POST` | `/login` | Authenticate user & get token | `email`, `password` |
| `POST` | `/logout` | Revoke current token | *None* |

---

### 2. Admin Capabilities (`/api/v1/admin`) - *Requires Admin Gate*
| Method | Endpoint | Description | Body Parameters |
| :--- | :--- | :--- | :--- |
| `GET` | `/admin/stats` | Get system aggregated stats | *None* |
| `GET` | `/admin/clubs/all` | List all clubs (including trashed) | *None* |
| `POST` | `/admin/clubs` | Create a new club | `name`, `description`, `leader_id` |
| `PUT` | `/admin/clubs/{id}/leader` | Change club leader | `leader_id` |
| `DELETE` | `/admin/clubs/{id}` | Soft delete a club | *None* |
| `POST` | `/admin/clubs/{id}/restore` | Restore a deleted club | *None* |

---

### 3. Student/Public Club Actions (`/api/v1/clubs`)
| Method | Endpoint | Description | Body Parameters |
| :--- | :--- | :--- | :--- |
| `GET` | `/clubs` | List all active public clubs | *None* |
| `GET` | `/my-clubs` | List user's approved & led clubs | *None* |
| `POST` | `/clubs/{id}/apply` | Send join request to a club | *None* |
| `DELETE`| `/clubs/{id}/leave` | Leave an approved club | *None* |

---

### 4. Leader Management (`/api/v1/clubs/{club_id}`) - *Requires Leader Ownership*
| Method | Endpoint | Description | Body Parameters |
| :--- | :--- | :--- | :--- |
| `GET` | `/members` | Get pending & approved members | *None* |
| `POST` | `/members/{user_id}/accept`| Accept pending member | *None* |
| `DELETE`| `/members/{user_id}/kick` | Remove an approved member | *None* |
| `POST` | `/events` | Create a new club event | `title`, `description`, `event_date`, `location` |
| `POST` | `/tasks` | Assign a task to a member | `title`, `description`, `due_date`, `assigned_to` |
| `POST` | `/announcements` | Post a club announcement | `title`, `content` |

---

### 5. Member Tasks & Attendance
| Method | Endpoint | Description | Body Parameters |
| :--- | :--- | :--- | :--- |
| `GET` | `/my-tasks` | Get assigned tasks (with club name) | *None* |
| `PUT` | `/tasks/{id}/complete` | Mark task as completed | *None* |
| `POST` | `/events/{event_id}/attendance` | Record member attendance | `user_id`, `status` (present/absent) |

---
**Standard Response Format:**
```json
{
  "status": "success",
  "message": "Operation completed successfully",
  "data": { ... }
}