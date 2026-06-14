<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobListing extends Model
{
  protected $fillable = [
    'user_id',
    'title',
    'location',
    'location_type',
    'min_salary',
    'max_salary',
    'job_type',
    'level',
    'application_deadline',
    'posted_date',
    'company_name',
    'company_description',
    'contact_person',
    'company_email',
    'department',
    'website',
  ];

  // convertit la valeur en objet de type Carbon
  protected function casts(): array
  {
    return [
      'application_deadline' => 'datetime',
      'posted_date' => 'datetime',
    ];
  }

  // relationships
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function description(): HasOne
  {
    return $this->hasOne(Description::class, 'job_listing_id');
  }

  public function companyLogo(): HasOne
  {
    return $this->hasOne(CompanyLogo::class, 'job_listing_id');
  }

  public function appliedJobs(): HasMany
  {
    return $this->hasMany(AppliedJob::class, 'job_id');
  }

}
