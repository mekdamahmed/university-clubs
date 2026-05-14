<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Attendance;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $events = Event::with('club')->where('event_date', '>=', now())->orderBy('event_date')->get();
        return $this->successResponse($events);
    }

    public function archived(Request $request)
    {
        $query = Event::withTrashed()->with('club')
            ->where(function($q) {
                $q->where('event_date', '<', now())
                  ->orWhereNotNull('deleted_at');
            });

        if ($request->has('club_id')) {
            $query->where('club_id', $request->club_id);
        }

        return $this->successResponse($query->orderBy('event_date', 'desc')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'club_id' => 'required|exists:clubs,id',
            'title' => 'required', 'description' => 'required',
            'event_date' => 'required|date', 'location' => 'required'
        ]);
        $event = Event::create($request->all());
        return $this->successResponse($event, 'Event created', 201);
    }

    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete(); 
        return $this->successResponse(null, 'Event deleted and archived successfully');
    }

    public function getAttendance($id)
    {
        $event = Event::withTrashed()->with('club.members')->findOrFail($id);
        $attendances = Attendance::where('event_id', $id)->get()->keyBy('user_id');
        
        $members = $event->club->members()->wherePivot('status', 'approved')->get()->map(function($user) use ($attendances) {
            $user->attendance_status = isset($attendances[$user->id]) ? $attendances[$user->id]->status : 'not_recorded';
            return $user;
        });

        return $this->successResponse($members);
    }

    public function takeAttendance(Request $request, $id)
    {
        $request->validate(['user_id' => 'required|exists:users,id', 'status' => 'required|in:present,absent']);
        Attendance::updateOrCreate(
            ['event_id' => $id, 'user_id' => $request->user_id],
            ['status' => $request->status]
        );
        return $this->successResponse(null, 'Attendance recorded');
    }
}