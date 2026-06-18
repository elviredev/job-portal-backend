<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
  public function register(Request $request)
  {
    // validate request data
    $validator = Validator::make($request->all(), [
      'firstName' => 'required|string|max:255',
      'lastName' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|confirmed|min:8',
      'role' => 'required|in:admin,user,recruiter',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors(), 422);
    }
    // create user in database
    $user = User::create([
      'first_name' => $request->firstName,
      'last_name' => $request->lastName,
      'email' => $request->email,
      'password' => Hash::make($request->password),
      'role' => $request->role,
    ]);

    // generate JWT token
    $token = JWTAuth::fromUser($user);

    // define environnment prod
    $isProd = app()->environment('production');

    $cookie = cookie([
      'auth_token',
      $token,
      60 * 24,                      // Minutes
      '/',                          // Path
      null,                         // Domain
      $isProd,                      // Secure en localhost (false en local et true en prod => https)
      true,                         // HttpOnly (prevent JS access, XSS)
      false,                        // Raw
      $isProd ? 'None' : 'Lax',     // SameSite en local (Essential for CSRF protection/auth)
    ]);

    return response()->json([
      'message' => 'User registered successfully',
      'user' => $user,
    ], 201)->withCookie($cookie);
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

    $token = JWTAuth::fromUser($user);

    // define environnment prod
    $isProd = app()->environment('production');

    $cookie = cookie([
      'auth_token',
      $token,
      60 * 24,                      // Minutes
      '/',                          // Path
      null,                         // Domain
      $isProd,                      // Secure en localhost (false en local et true en prod => https)
      true,                         // HttpOnly (prevent JS access, XSS)
      false,                        // Raw
      $isProd ? 'None' : 'Lax',     // SameSite en local (Essential for CSRF protection/auth)
    ]);

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
}
