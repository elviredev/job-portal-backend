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
    try {
      // récupérer user authentifié
      $user = JWTAuth::parseToken()->authenticate();

      $applied = AppliedJob::where('user_id', $user->id)
        ->where('job_id', $jobId)
        ->exists();

      return response()->json([
        'applied' => $applied,
      ]);

    } catch (Exception $e) {
      return response()->json([
        'applied' => false,
      ]);
    }
  }
}
