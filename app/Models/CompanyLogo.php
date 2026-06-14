<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyLogo extends Model
{
  protected $fillable = [
    'job_listing_id',
    'original_name',
    'logo_path',
  ];

  public function jobListing(): BelongsTo
  {
    return $this->belongsTo(JobListing::class, 'job_listing_id');
  }
}
