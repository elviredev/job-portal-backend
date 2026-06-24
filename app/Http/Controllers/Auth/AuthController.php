<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserImage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
  public function register(Request $request)
  {
    // validate request data
    $validator = Validator::make($request->all(), [
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'email' => 'required|string|email:rfc|max:255|unique:users,email',
      'password' => 'required|confirmed|min:8',
      'role' => 'required|in:user,recruiter',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'errors' => $validator->errors()
      ], 422);
    }
    // create user in database
    $user = User::create([
      'first_name' => $request->first_name,
      'last_name' => $request->last_name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
      'role' => $request->role,
    ]);

    return response()->json([
      'message' => 'User registered successfully',
      'user' => $user,
    ], 201);
  }

  public function login(Request $request)
  {
    $request->validate([
      'email' => 'required|email',
      'password' => 'required|string|min:8',
      'role' => 'required|in:admin,user,recruiter',
    ]);

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
      return response()->json([
        'error' => 'Invalid credentials'
      ], 401);
    }

    if ($user->role !== $request->role) {
      return response()->json([
        'error' => "This account is registered as a $user->role. Please use the correct login form."
      ], 403);
    }

    if (!$user->is_active) {
      return response()->json([
        'error' => 'This account is deactivated. Please contact support.',
      ], 403);
    }

    // generate JWT token
    $token = JWTAuth::fromUser($user);

    // define environnment prod
    $isProd = app()->environment('production');

    // poser un cookie
    $cookie = Cookie::make(
      'auth_token',
      $token,
      60 * 24,                      // Minutes
      '/',                          // Path
      null,                         // Domain
      $isProd,                      // Secure en localhost (false en local et true en prod => https)
      true,                         // HttpOnly (prevent JS access, XSS)
      false,                        // Raw
      $isProd ? 'None' : 'Lax',     // SameSite en local (Essential for CSRF protection/auth)
    );

    return response()->json([
      'success' => 'You are logged successfully',
      'user' => [
        'full_name' => $user->full_name,
        'role' => $user->role,
        'email' => $user->email
      ],
    ])->withCookie($cookie);
  }

  public function me()
  {
    try {
      // récupération user via le JWT : laravel lit le cookie "auth_token", extrait le JWT, vérifie validité du token, récupère le user associé
      $user = JWTAuth::parseToken()->authenticate();

      $user->load('image');
      $image = $user->image?->image_path;

      return response()->json([
        'status' => 'success',
        'user' => [
          'id' => $user->id,
          'first_name' => $user->first_name,
          'last_name' => $user->last_name,
          'full_name' => $user->full_name,
          'email' => $user->email,
          'role' => $user->role,
          'is_active' => $user->is_active,
          'image' => $image
            ? (str_starts_with($image, 'http')
              ? $image
              : asset('storage/'.$image))
            : null,
        ]
      ]);
    }  catch (JWTException $e) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }
  }

  public function logout()
  {
    try {
      // invalidate JWT token
      JWTAuth::invalidate(JWTAuth::getToken());

      return response()->json([
        'message' => 'You are logged out successfully'
      ])->withCookie(cookie()->forget('auth_token'));
    } catch(Exception $e) {
      return response()->json(['error' => 'Could not log out'], 500);
    }
  }

  public function updateProfile(Request $request)
  {
    try {
      // récupérer user authentifié
      $user = JWTAuth::parseToken()->authenticate();

      $validator = Validator::make($request->all(), [
        'first_name' => 'sometimes|string|max:255',
        'last_name' => 'sometimes|string|max:255',
        'image' => 'sometimes|image|mimetypes:image/jpeg,image/png,image/webp,image/avif|max:4096',
        'google_image_url' => 'nullable|url'
      ]);

      if ($validator->fails()) {
        return response()->json([
          'message' => $validator->errors()->first()
        ], 422);
      }

      // update name
      if ($request->filled('first_name')) $user->first_name = $request->first_name;
      if ($request->filled('last_name')) $user->last_name = $request->last_name;
      $user->save();

      // update image
      if ($request->hasFile('image')) {
        $file = $request->file('image');
        $path = $file->store('profile_images', 'public');

        // if image exists
        $this->deletingExistingImage($user);

        UserImage::updateOrCreate(
          ['user_id' => $user->id],
          ['image_path' => $path]
        );
      } elseif ($request->filled('google_image_url')) {
        $this->deletingExistingImage($user);

        UserImage::updateOrCreate(
          ['user_id' => $user->id],
          ['image_path' => $request->google_image_url]
        );
      }

      $user->load('image');

      return response()->json([
        'status' => 'success',
        'message' => 'Profile updated successfully',
        'user' => new UserResource($user)
      ]);

    } catch (Exception $e) {
      return response()->json(['message' => 'Unauthorized'], 401);
    }
  }

  /**
   * @desc Suppression de l'image existante
   * @param User $user
   * @return void
   */
  private function deletingExistingImage(User $user): void
  {
    $existingImage = UserImage::where('user_id', $user->id)->first();

    if (!$existingImage) {
      return;
    }

    if (!str_starts_with($existingImage->image_path, 'http')) {
      Storage::disk('public')->delete($existingImage->image_path);
    }

    $existingImage->delete();
  }
}
