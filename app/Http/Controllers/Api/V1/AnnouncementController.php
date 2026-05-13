<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    use ApiResponse;

    // View all announcements
    public function index()
    {
        $announcements = Announcement::with(['club', 'author'])->latest()->get();
        return $this->successResponse($announcements);
    }

    // Leader: Create announcement
    public function store(Request $request)
    {
        $request->validate([
            'club_id' => 'required|exists:clubs,id',
            'title' => 'required|string',
            'content' => 'required|string'
        ]);

        $announcement = Announcement::create([
            'club_id' => $request->club_id,
            'author_id' => $request->user()->id,
            'title' => $request->title,
            'content' => $request->content
        ]);

        return $this->successResponse($announcement, 'Announcement posted successfully!', 201);
    }
}