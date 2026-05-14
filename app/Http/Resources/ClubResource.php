<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClubResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'leader_name' => $this->leader ? $this->leader->name : 'No Leader',
            'created_at' => $this->created_at->format('Y-m-d'),
        ];
    }
}