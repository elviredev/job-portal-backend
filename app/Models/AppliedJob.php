<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppliedJob extends Model
{
  protected $fillable = [
    'user_id',
    'job_id',
    'first_name',
    'last_name',
    'email',
    'linkedin',
    'resume',
    'status',
  ];

  // relationships
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function job(): BelongsTo
  {
    return $this->belongsTo(JobListing::class, 'job_id');
  }
}

