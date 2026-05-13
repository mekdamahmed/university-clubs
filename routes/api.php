<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ClubController;
use App\Http\Controllers\Api\V1\AdminController;
use App\Http\Controllers\Api\V1\MemberController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\AnnouncementController;

Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        
        // Public Auth Data
        Route::get('/clubs', [ClubController::class, 'index']);
        Route::post('/clubs/{id}/apply', [ClubController::class, 'apply']);
        
        Route::get('/my-clubs', [ClubController::class, 'myClubs']);

        Route::get('/events/upcoming', [EventController::class, 'index']);
        Route::get('/events/archived', [EventController::class, 'archived']);
        
        Route::get('/my-tasks', [TaskController::class, 'myTasks']);
        Route::post('/tasks/{id}/complete', [TaskController::class, 'complete']);

        // Admin & Leader Routes (Protected by Gates inside controllers/providers)
        Route::get('/admin/stats', [AdminController::class, 'stats']);
        Route::get('/admin/clubs/all', [ClubController::class, 'adminIndex']);
        Route::post('/admin/clubs', [ClubController::class, 'store']);
        Route::put('/admin/clubs/{id}/leader', [ClubController::class, 'update']); // Change Leader
        
        // Club Management (Admin + Leader)
        Route::get('/clubs/{id}/members', [MemberController::class, 'index']);
        Route::post('/clubs/{id}/members/{user_id}/accept', [MemberController::class, 'accept']);
        Route::post('/clubs/{id}/members/{user_id}/kick', [MemberController::class, 'kick']);
        
        Route::post('/events', [EventController::class, 'store']);
        Route::delete('/events/{id}', [EventController::class, 'destroy']); // <-- أضف هذا السطر
        Route::get('/events/{id}/attendance', [EventController::class, 'getAttendance']);
        Route::post('/events/{id}/attendance', [EventController::class, 'takeAttendance']);

        Route::get('/clubs/{id}/tasks', [TaskController::class, 'clubTasks']); // Leader view tasks
        Route::post('/tasks', [TaskController::class, 'store']);

        Route::get('/announcements', [AnnouncementController::class, 'index']);
        Route::post('/announcements', [AnnouncementController::class, 'store']);
    });
});