<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobRequest;
use App\Models\CompanyLogo;
use App\Models\Description;
use App\Models\JobListing;
use Illuminate\Http\Request;

class JobController extends Controller
{
  public function index(Request $request)
  {
    $query = JobListing::with(['description', 'companyLogo'])->latest('posted_date');

    // choix dans la homepage pour choisir le nb de jobs à afficher
    $perPage = in_array((int)$request->per_page, [9, 18, 50, 100])
      ? (int)$request->per_page
      : 9;

    // filtres - recherche
    if ($request->filled('keyword')) {
      $query->where(function ($q) use ($request) {
        $keyword = strtolower($request->keyword);
        $q->whereRaw('LOWER(title) LIKE ?', ['%'.$keyword.'%'])
          ->orWhereRaw('LOWER(department) LIKE ?', ['%'.$keyword.'%']);
      });
    }

    if ($request->filled('location')) {
      $query->where(function ($q) use ($request) {
        $location = strtolower($request->location);
        $q->whereRaw('LOWER(location) LIKE ?', ['%'.$location.'%'])
          ->orWhereRaw('LOWER(location_type) LIKE ?', ['%'.$location.'%']);
      });
    }

    if ($request->filled('min_salary')) {
      $query->where('min_salary', '>=', $request->min_salary);
    }

    if ($request->filled('job_type')) {
      $types = explode(',', $request->job_type);
      $query->whereIn('job_type', $types);
    }

    if ($request->filled('location_type')) {
      $types = explode(',', $request->location_type);
      $query->whereIn('location_type', $types);
    }

    $jobs = $query->paginate($perPage);
    $mapped = $jobs->getCollection()->map(function ($job) {
      return [
        'id' => $job->id,
        'title' => $job->title,
        'department' => $job->department,
        'level' => $job->level,
        'location' => $job->location,
        'location_type' => $job->location_type,
        'job_type' => $job->job_type,
        'posted_date' => $job->posted_date?->format('Y-m-d'),
        'application_deadline' => $job->application_deadline?->format('Y-m-d'),
        'min_salary' => $job->min_salary,
        'max_salary' => $job->max_salary,
        'company_name' => $job->company_name,
        'company_email' => $job->company_email,
        'company_description' => $job->company_description,
        'website' => $job->website,
        'contact_person' => $job->contact_person,
        'description' => $job->description,
        'company_logo_url' => $job->companyLogo
          ? url('storage/' .$job->companyLogo->logo_path)
          : null,
      ];
    })->values();

    return response()->json([
      'status' => 'success',
      'data' => $mapped,
      'meta' => [
        'current_page' => $jobs->currentPage(),
        'last_page' => $jobs->lastPage(),
        'total' => $jobs->total(),
        'per_page' => $jobs->perPage(),
      ]
    ], 200);
  }


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
      'user_id' => auth('api')->id(),
      // 'user_id' => 1, // dummy value for testing
    ]);

    $description = Description::create([
      'job_listing_id' => $jobListing->id,
      'key_role' => $validated['key_role'],
      'responsability' => $validated['responsability'],
      'skill_and_experience' => $validated['skill_and_experience'],
    ]);

    // handle logo if exist
    $company_logo = null;
    if ($request->hasFile('company_logo')) {
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
      ],
    ], 201);

  }
}
