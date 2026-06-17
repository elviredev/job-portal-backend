<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

#[Fillable(['first_name', 'last_name', 'email', 'password', 'role', 'is_active', 'google_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{
  /** @use HasFactory<UserFactory> */
  use HasFactory, Notifiable;

  /**
   * Get the attributes that should be cast.
   *
   * @return array<string, string>
   */
  protected function casts(): array
  {
    return [
      'email_verified_at' => 'datetime',
      'password' => 'hashed',
      'is_active' => 'boolean',
    ];
  }

  // relationships
  // cet user possède une seule image. Clé étrangère 'user_id' dans table 'user_images'
  public function image(): HasOne
  {
    return $this->hasOne(UserImage::class, 'user_id');
  }

  public function jobListings(): HasMany
  {
    return $this->hasMany(JobListing::class, 'user_id');
  }

  /**
   * Récupérer l'identifiant qui sera stocké dans la revendication « subject » du JWT.
   *
   * @return mixed
   */
  public function getJWTIdentifier(): mixed
  {
    return $this->getKey();
  }

  public function getJWTCustomClaims(): array
  {
    return ['role' => $this->role];
  }

  // pour Google Login car dans notre bdd on a pas de colonne "fullname"
  public function getFullNameAttribute(): string
  {
    return "{$this->first_name} {$this->last_name}";
  }

}
