<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyJobRequest;
use App\Http\Resources\AppliedJobResource;
use App\Models\AppliedJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SavedJobController extends Controller
{
  /**
   * @desc Postuler à un job
   * @param ApplyJobRequest $request
   * @return JsonResponse
   */
  public function apply(ApplyJobRequest $request): JsonResponse
  {
    // user check
    $user = auth()->user();

    // validation
    $validated = $request->validated();

    // check duplicated application
    $alreadyApplied = AppliedJob::where('user_id', $user->id)
      ->where('job_id', $validated['job_id'])
      ->exists();

    if ($alreadyApplied) {
      return response()->json([
        'message' => 'You have already applied for this job.',
      ], 409);
    }

    // resume upload
    $resumePath = null;
    if ($request->hasFile('resume')) {
      $file = $request->file('resume');
      $fileName = time().'_'.$user->id.'_'.Str::random(10).'.pdf';
      $resumePath = $file->storeAs('resumes', $fileName, 'public');
    }

    // créer une candidature
    $application = AppliedJob::create([
      'user_id' => $user->id,
      'job_id' => $validated['job_id'],
      'first_name' => $validated['first_name'],
      'last_name' => $validated['last_name'],
      'email' => $validated['email'],
      'linkedin' => $validated['linkedin'] ?? null,
      'resume' => $resumePath,
      'status' => 'applied',
    ]);

    return response()->json([
      'status' => 'success',
      'message' => 'Application submitted successfully',
      'data' => new AppliedJobResource($application),
    ], 201);

  }

  /**
   * @desc Vérifier si un jon a été postulé par un user connecté ou pas
   * @param $jobId
   * @return JsonResponse
   */
  public function checkApplied($jobId): JsonResponse
  {
    // récupérer user authentifié
    $user = auth()->user();

    $applied = AppliedJob::where('user_id', $user->id)
      ->where('job_id', $jobId)
      ->exists();

    return response()->json([
      'applied' => $applied,
    ]);
  }
}
