<?php

namespace App\Http\Controllers;

use App\Models\AppliedJob;
use Exception;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class SavedJobController extends Controller
{
  public function checkApplied($jobId)
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
