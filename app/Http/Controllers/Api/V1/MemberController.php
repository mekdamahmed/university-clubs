<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Task;
use App\Models\Attendance;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    use ApiResponse;

    public function index(Request $request, $club_id)
    {
        // جلب النادي مع بيانات الليدر
        $club = Club::with('leader')->findOrFail($club_id);
        $pending = $club->members()->wherePivot('status', 'pending')->get();
        
        // حساب إحصائيات الأعضاء العاديين
        $approved = $club->members()->wherePivot('status', 'approved')->get()->map(function($user) use ($club_id) {
            
            // حساب التاسكات المكتملة بدقة
            $user->completed_tasks = Task::where('assigned_to', $user->id)
                                         ->where('club_id', $club_id)
                                         ->where('is_completed', true)
                                         ->count();
                                         
            // حساب الغيابات (السر هنا: withTrashed لجلب الغيابات حتى من الإيفنتات المؤرشفة)
            $user->absences = Attendance::where('user_id', $user->id)
                                        ->where('status', 'absent')
                                        ->whereHas('event', function($q) use ($club_id) {
                                            $q->withTrashed()->where('club_id', $club_id); // <--- الحل هنا
                                        })->count();
            return $user;
        });

        // حساب إحصائيات الليدر بدقة (إذا كان الليدر موجوداً)
        $leader = $club->leader;
        if ($leader) {
            $leader->completed_tasks = Task::where('assigned_to', $leader->id)
                                           ->where('club_id', $club_id)
                                           ->where('is_completed', true)
                                           ->count();
                                           
            // إجبار لارافيل على حساب غيابات الليدر حتى في الإيفنتات المؤرشفة
            $leader->absences = Attendance::where('user_id', $leader->id)
                                          ->where('status', 'absent')
                                          ->whereHas('event', function($q) use ($club_id) {
                                              $q->withTrashed()->where('club_id', $club_id); // <--- الحل هنا
                                          })->count();
        }

        return $this->successResponse([
            'leader' => $leader,
            'pending_requests' => $pending,
            'approved_members' => $approved
        ]);
    }

    public function accept(Request $request, $club_id, $user_id)
    {
        $club = Club::findOrFail($club_id);
        $club->members()->updateExistingPivot($user_id, [
            'status' => 'approved',
            'joined_at' => now()
        ]);

        return $this->successResponse(null, 'Member accepted successfully');
    }

    public function kick(Request $request, $club_id, $user_id)
    {
        $club = Club::findOrFail($club_id);
        $club->members()->detach($user_id);

        return $this->successResponse(null, 'Member removed successfully');
    }
}