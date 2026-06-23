<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Http\Resources\JobListingResource;
use App\Models\CompanyLogo;
use App\Models\Description;
use App\Models\JobListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class JobController extends Controller
{
  public function index(Request $request)
  {
    $request->validate([
      'per_page' => 'nullable|integer|in:9,18,50,100',
      'page' => 'nullable|integer|min:1',
      'keyword' => 'nullable|string|max:255',
      'location' => 'nullable|string|max:255',
      'min_salary' => 'nullable|numeric|min:0',
      'job_type' => 'nullable|string',
      'location_type' => 'nullable|string',
    ]);

    $query = JobListing::with(['description', 'companyLogo'])->latest('posted_date');

    // choix dans la homepage pour choisir le nb de jobs à afficher
    $perPage = in_array((int)$request->per_page, [9, 18, 50, 100])
      ? (int)$request->per_page
      : 9;

    // filtres - recherche
    if ($request->filled('keyword')) {
      $query->where(function ($q) use ($request) {
        $q->where('title', 'like', '%'.$request->keyword.'%')
          ->orWhere('department', 'like', '%'.$request->keyword.'%');
      });
    }

    if ($request->filled('location')) {
      $query->where(function ($q) use ($request) {
        $q->where('location', 'like', '%'.$request->location.'%')
          ->orWhere('location_type', 'like', '%'.$request->location.'%');
      });
    }

    if ($request->filled('min_salary')) {
      $query->where('max_salary', '>=', $request->min_salary);
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

    return response()->json([
      'status' => 'success',
      'data' => JobListingResource::collection($jobs),
      'meta' => [
        'current_page' => $jobs->currentPage(),
        'last_page' => $jobs->lastPage(),
        'total' => $jobs->total(),
        'per_page' => $jobs->perPage(),
      ],
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

  // my jobs for recruiter
  public function myJobs()
  {
    $jobs = JobListing::with(['description', 'companyLogo'])
      ->where('user_id', auth('api')->id())
      ->latest()
      ->get();

    return response()->json([
      'status' => 'success',
      'data' => JobListingResource::collection($jobs),
    ], 200);
  }

  /** public show job by id */
  public function showPublic(JobListing $job)
  {
    $job->load(['description', 'companyLogo']);

    // dd((new JobListingResource($job))->toArray(request()));
    return response()->json([
      'status' => 'success',
      'data' => new JobListingResource($job),
    ], 200);
  }

  public function show(JobListing $job)
  {
    // vérifie que le propriétaire est bien le user connecté
    abort_if($job->user_id !== auth('api')->id(), 403);

    // charge les relations nécessairees
    $job->load(['description', 'companyLogo', 'user']);

    // retourner la resource
    return response()->json([
      'status' => 'success',
      'data' => new JobListingResource($job),
    ], 200);
  }

  /** @throws Throwable */
  public function update(UpdateJobRequest $request, JobListing $job)
  {
    abort_if($job->user_id !== auth('api')->id(), 403);

    $validated = $request->validated();

    // ajouter une transaction pour éviter des incohérences de données
    // si une erreur survient après la mise à jour du job mais avant celle du logo par ex
    DB::transaction(function () use ($validated, $job, $request) {

      $job->update([
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
      ]);

      // update description linked to this job
      $job->description()->updateOrCreate(
        ['job_listing_id' => $job->id],
        [
          'key_role' => $validated['key_role'],
          'responsability' => $validated['responsability'],
          'skill_and_experience' => $validated['skill_and_experience'],
        ]
      );

      // update company logo
      $logo = $job->companyLogo;
      if ($request->hasFile('company_logo')) {

        if ($logo) {
          // Supprime l'ancien fichier physique
          Storage::disk('public')->delete($logo->logo_path);
        }

        $file = $request->file('company_logo');

        // Met à jour l'enregistrement existant ou en créé un si aucun logo existant
        $job->companyLogo()->updateOrCreate(
          ['job_listing_id' => $job->id],
          [
            'original_name' => $file->getClientOriginalName(),
            'logo_path' => $file->store('company_logos', 'public'),
          ]
        );

      }
    });

    // reload relationships
    $job->load(['description', 'companyLogo']);

    return response()->json([
      'status' => 'success',
      'message' => 'Job listing updated successfully!',
      'data' => new JobListingResource($job),
    ], 200);

  }

  public function destroy(JobListing $job)
  {
    // vérifie si c'est bien le job du user connecté
    if ($job->user_id !== auth('api')->id()) {
      abort(403);
    }

    // suppression du fichier image
    if ($job->companyLogo?->logo_path) {
      Storage::disk('public')->delete($job->companyLogo->logo_path);
    }

    $job->delete();

    return response()->json([
      'status' => 'success',
      'message' => 'Job deleted successfully! ',
    ]);
  }

}
