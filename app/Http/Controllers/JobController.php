<?php

namespace App\Http\Controllers;

use App\Models\CompanyLogo;
use App\Models\Description;
use App\Models\JobListing;
use Illuminate\Http\Request;

class JobController extends Controller
{
  public function store(Request $request)
  {
    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'department' => 'required|string|max:100',
      'level' => 'required|in:intern,junior,mid,senior,lead,manager',
      'location' => 'required|string|max:100',
      'location_type' => 'required|in:remote,on-site,hybrid',
      'job_type' => 'required|in:full-time,part-time,contract,internship,freelance',
      'application_deadline' => 'nullable|date',
      'min_salary' => 'required|numeric|min:0',
      'max_salary' => 'required|numeric|min:0|gt:min_salary',
      'company_name' => 'required|string|max:255',
      'website' => 'nullable|url|max:2048',
      'contact_person' => 'required|string|max:200',
      'company_email' => 'required|email|max:255',
      'company_description' => 'nullable|string',

      // descriptions
      'key_role' => 'required|string',
      'responsability' => 'required|string',
      'skill_and_experience' => 'required|string',

      // company logo
      'company_logo' => 'nullable|file|image|mimes:jpeg,png,jpg,webp,avif|max:4048',
    ]);

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
