<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobRequest;
use App\Models\CompanyLogo;
use App\Models\Description;
use App\Models\JobListing;
use Illuminate\Http\Request;

class JobController extends Controller
{
  public function store(StoreJobRequest $request)
  {
    $validated = $request->validated();

    $jobListing = JobListing::create([
      'title' => $validated['title'],
      'department' => $validated['department'],
      'level' => $validated['level'],
      'location' => $validated['location'] ?? null,
      'location_type' => $validated['location_type'],
      'job_type' => $validated['job_type'],
      'application_deadline' => $validated['application_deadline'] ?? null,
      'min_salary' => $validated['min_salary'],
      'max_salary' => $validated['max_salary'] ?? null,
      'company_name' => $validated['company_name'],
      'website' => $validated['website'] ?? null,
      'contact_person' => $validated['contact_person'],
      'company_email' => $validated['company_email'],
      'company_description' => $validated['company_description'] ?? null,
      'posted_date' => now(),
      // 'user_id' => auth('api')->id(),
      'user_id' => 1, // dummy value for testing
    ]);

    $description = Description::create([
      'job_listing_id' => $jobListing->id,
      'key_role' => $validated['key_role'],
      'responsability' => $validated['responsability'],
      'skill_and_experience' => $validated['skill_and_experience'],
    ]);

    // handle logo if exist
    $company_logo = null;
    if($request->hasFile('company_logo')) {
      $file = $request->file('company_logo');
      $originalName = $file->getClientOriginalName();
      $path = $file->store('company_logos', 'public');

      $company_logo = CompanyLogo::create([
        'job_listing_id' => $jobListing->id,
        'original_name' => $originalName,
        'logo_path' => $path,
      ]);
    }

    return response()->json([
      'status' => 'success',
      'message' => 'Job listing created successfully!',
      'data' => [
        'job_listing' => $jobListing,
        'description' => $description,
        'company_logo' => $company_logo,
      ]
    ], 201);

  }
}
