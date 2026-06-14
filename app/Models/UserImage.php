<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserImage extends Model
{
  protected $fillable = [
    'user_id',
    'image_path',
  ];

  // relationships
  // cette image appartient à un user. Clé étrangère 'user_id' dans cette table 'user_images'
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
