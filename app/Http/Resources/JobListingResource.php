<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobListingResource extends JsonResource
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
      'title' => $this->title,
      'department' => $this->department,
      'level' => $this->level,
      'location' => $this->location,
      'location_type' => $this->location_type,
      'job_type' => $this->job_type,

      'posted_date' => $this->posted_date?->format('Y-m-d'),
      'application_deadline' => $this->application_deadline?->format('Y-m-d'),

      'min_salary' => $this->min_salary,
      'max_salary' => $this->max_salary,

      'company_name' => $this->company_name,
      'company_email' => $this->company_email,
      'company_description' => $this->company_description,
      'website' => $this->website,
      'contact_person' => $this->contact_person,

      'description' => $this->description,

      'company_logo_url' => $this->companyLogo
        ? url('storage/'.$this->companyLogo->logo_path)
        : null,
    ];
  }
}
