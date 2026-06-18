<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserImage;
use Illuminate\Http\Request;
use Google_Client;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class GoogleLoginController extends Controller
{
  public function googleLogin(Request $request)
  {
    $request->validate([
      'token' => 'required|string',
      'role' => 'required|in:user,recruiter', // compte admin uniquement créé manuellement
    ]);

    // verify google token
    $client = new Google_Client(['client_id' => config('services.google.client_id')]);
    $payload = $client->verifyIdToken($request->token);
    // return response()->json($payload);
    if (!$payload) {
      return response()->json([
        'error' => 'Invalid google token',
      ], 401);
    }

    // verify google email verified
    if (!($payload['email_verified'] ?? false)) {
      return response()->json([
        'error' => 'Google email is not verified.',
      ], 401);
    }

    // extract google info
    $googleId = $payload['sub'];
    $email = $payload['email'];
    $googleImage = $payload['picture'] ?? null;
    $targetRole = $request->role;

    // try to find google id or email in bdd
    $user = User::where('google_id', $googleId)
      ->orWhere('email', $email)
      ->first();

    if ($user) {
      // security check
      if ($user->role !== $targetRole) {
        return response()->json([
          'error' => "This account is registered as a $user->role. Please use the correct login form.",
        ], 403);
      }

      // update google id if it wasn't set yet
      if (!$user->google_id) {
        $user->update(['google_id' => $googleId]);
      }
    } else {
      // register new user if not found
      $nameParts = explode(' ', $payload['name'], 2);
      $user = User::create([
        'first_name' => $payload['given_name'] ?? $nameParts[0],
        'last_name' => $payload['family_name'] ?? $nameParts[1] ?? '',
        'email' => $email,
        'password' => bcrypt(Str::random(24)),
        'role' => $targetRole,
        'google_id' => $googleId,
        'is_active' => true,
      ]);
    }

    if (!$user->is_active) {
      return response()->json([
        'error' => 'This account is deactivated. Please contact support.',
      ], 403);
    }

    // save image
    if ($googleImage) {
      $existingImage = UserImage::where('user_id', $user->id)->first();

      // only update if no image
      if (!$existingImage || str_starts_with($existingImage->image_path, 'http')) {
        UserImage::updateOrCreate(
          ['user_id' => $user->id],
          ['image_path' => $googleImage]
        );
      }
    }

    // generate JWT
    $token = JWTAuth::fromUser($user);

    // get cookie settings from config or helper
    $isProd = app()->environment('production');

    // rafracihir la relation
    $user->load('image');

    $image = $user->image?->image_path;

    // return http only
    return response()->json([
      'success' => 'Login successfully',
      'user' => [
        'full_name' => $user->full_name,
        'first_name' => $user->first_name,
        'role' => $user->role,
        'email' => $user->email,
        'image' => $image
          ? (str_starts_with($image, 'http')
            ? $image
            : asset('storage/'.$image))
          : null,
      ],
    ])->cookie(
      'auth_token',
      $token,
      60 * 24,                      // Minutes
      '/',                          // Path
      null,                         // Domain
      $isProd,                      // Secure en localhost (false en local et true en prod => https)
      true,                         // HttpOnly (prevent JS access, XSS)
      false,                        // Raw
      $isProd ? 'None' : 'Lax'      // SameSite en local (Essential for CSRF protection/auth)
    );
  }
}
