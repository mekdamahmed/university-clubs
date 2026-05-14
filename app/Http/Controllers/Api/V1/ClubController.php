<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ClubController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $clubs = Club::with('leader')->get()->map(function ($club) use ($request) {
            $status = 'none';
            if ($request->user()) {
                if ($club->leader_id === $request->user()->id) { $status = 'leader'; }
                else {
                    $member = $club->members()->where('user_id', $request->user()->id)->first();
                    if ($member) { $status = $member->pivot->status; } // 'pending' or 'approved'
                }
            }
            $club->user_status = $status;
            return $club;
        });

        return $this->successResponse($clubs, 'Clubs retrieved');
    }

    public function adminIndex()
    {
        return $this->successResponse(Club::withTrashed()->with('leader')->get());
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required', 'leader_id' => 'required|exists:users,id']);
        $club = Club::create($request->all());
        return $this->successResponse($club, 'Club created successfully', 201);
    }


    public function update(Request $request, $id)
    {
        $request->validate(['leader_id' => 'required|exists:users,id']);
        $club = Club::findOrFail($id);
        $club->update(['leader_id' => $request->leader_id]);
        return $this->successResponse($club, 'Club leader updated successfully');
    }

    public function apply(Request $request, $id)
    {
        $club = Club::findOrFail($id);
        $user = $request->user();

        if ($club->leader_id === $user->id) { return $this->errorResponse('You lead this club!', 400); }
        if ($club->members()->where('user_id', $user->id)->exists()) {
            return $this->errorResponse('You are already a member or pending!', 400);
        }

        $club->members()->attach($user->id, ['status' => 'pending']);
        return $this->successResponse(null, 'Application sent successfully');
    }

    public function myClubs(Request $request)
    {
        $user = $request->user();
        
        $memberClubs = $user->clubs()->wherePivot('status', 'approved')->withPivot('joined_at')->get();
        
        $ledClubs = $user->ledClubs()->get()->map(function($club) {
            $club->pivot = (object) ['joined_at' => $club->created_at]; 
            $club->is_leader_badge = true; 
            return $club;
        });

        $allClubs = $memberClubs->merge($ledClubs)->unique('id')->values();

        return $this->successResponse($allClubs, 'My clubs retrieved');
    }
    




    public function destroy($id)
    {
        $club = \App\Models\Club::findOrFail($id);
        $club->delete(); 
        return $this->successResponse(null, 'Club deleted successfully');
    }

    public function restore($id)
    {
        $club = \App\Models\Club::withTrashed()->findOrFail($id);
        $club->restore();
        return $this->successResponse(null, 'Club restored successfully');
    }
}