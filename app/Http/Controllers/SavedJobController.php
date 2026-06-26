<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyJobRequest;
use App\Http\Resources\AppliedJobResource;
use App\Models\AppliedJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
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

  /**
   * @desc Récupérer les candidatures d'un user connecté
   * @return JsonResponse
   */
  public function getAppliedJobs()
  {
    // on utilise auth()->id() car on a besoin que de l'id du user
    $applications = AppliedJob::with(['job.companyLogo'])
      ->where('user_id', auth()->id())
      ->latest()
      ->get();

    return response()->json([
      'status' => 'success',
      'data' => AppliedJobResource::collection($applications)
    ], 200);
  }

  /**
   * @desc Supprimer une candidature
   * @param $id
   * @return JsonResponse
   */
  public function destroy($id)
  {
    $application = AppliedJob::where('id', $id)
      ->where('user_id', auth()->id())
      ->firstOrFail();

    if (!$application) {
      return response()->json([
        'message' => 'Application not found',
      ], 404);
    }

    // delete resume file if exists
    if ($application->resume) {
      Storage::disk('public')->delete($application->resume);
    }

    $application->delete();

    return response()->json([
      'status' => 'success',
      'message' => 'Application deleted successfully',
    ], 200);
  }
}
