<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Event;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    use ApiResponse; 

    public function stats(Request $request)
    {
        $clubsCount = Club::count();
        $eventsCount = Event::count();
        $activeMembers = DB::table('club_user')->where('status', 'approved')->distinct('user_id')->count();

        return $this->successResponse([
            'total_clubs' => $clubsCount,
            'total_events' => $eventsCount,
            'active_members' => $activeMembers
        ], 'Admin stats retrieved successfully');
    }
}