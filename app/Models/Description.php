<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Description extends Model
{
  protected $fillable = [
    'job_listing_id',
    'key_role',
    'responsibility',
    'skill_and_experience',
  ];

  // relationship
  public function jobListing(): BelongsTo
  {
    return $this->belongsTo(JobListing::class, 'job_listing_id');
  }
}
