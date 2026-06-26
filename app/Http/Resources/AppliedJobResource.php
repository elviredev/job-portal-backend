<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class AppliedJobResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,

      'user_id' => $this->user_id,
      'job_id' => $this->job_id,

      'first_name' => $this->first_name,
      'last_name' => $this->last_name,
      'email' => $this->email,

      'linkedin' => $this->linkedin,

      'resume' => $this->resume,
      'resume_url' => $this->resume
        ? Storage::disk('public')->url($this->resume)
        : null,

      'status' => $this->status,
      'created_at' => $this->created_at?->toISOString(),

      'job' => new JobListingResource($this->whenLoaded('job')),
    ];
  }
}
