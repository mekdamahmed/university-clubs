# 🎓 University Clubs Management System

## 📝 Overview
An Enterprise-level Single Page Application (SPA) designed to manage university student clubs. It features a robust Role-Based Access Control (RBAC) system for Admins, Club Leaders, and Students. Built with Laravel (RESTful API) and Vanilla JavaScript.

## 🚀 Key Features
*   **Role-Based Access Control:** Distinct dashboards and permissions for Admins, Leaders, and Students.
*   **Dynamic Data Rendering:** Vanilla JS SPA architecture with Fetch API for seamless UI updates without page reloads.
*   **Secure Authentication:** Powered by Laravel Sanctum (Token-based Auth).
*   **Soft Deletes & Archiving:** Safe deletion of clubs and events to maintain Data Integrity and historical attendance records.
*   **Advanced Relationships:** Custom Pivot table logic for managing memberships and statuses (Pending/Approved).

## 🛠️ Tech Stack
*   **Backend:** Laravel 11.x (PHP)
*   **Database:** MySQL (Eloquent ORM, Migrations, Foreign Keys Cascade)
*   **Frontend:** HTML5, CSS3 (Glassmorphism design), Vanilla JavaScript
*   **Security:** Laravel Sanctum, BCrypt Hashing, Custom Gates

## ⚙️ Setup & Installation Instructions

Follow these steps to run the project locally:

1. **Prerequisites:**
   Ensure you have [XAMPP](https://www.apachefriends.org/), [Composer](https://getcomposer.org/), and PHP >= 8.2 installed.

2. **Start Local Server:**
   Open XAMPP and start **Apache** and **MySQL**.

3. **Create Database:**
   Open phpMyAdmin (`http://localhost/phpmyadmin`) and create an empty database named: `club_management`

4. **Install Dependencies:**
   Open your terminal in the project directory and run:
   ```bash
   composer install```

5. Environment Configuration:
    Copy the example .env file:
    ```cp .env.example .env```

   Open the `.env` file and ensure the database credentials are correct:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=club_management
   DB_USERNAME=root
   DB_PASSWORD=```

6.  Generate App Key & Migrate Database:
    Run the following commands to secure the app and build the tables:
    ```bash php artisan key:generate 
    php artisan migrate:fresh```

7. Run the Application:
   ```bash php artisan serve```