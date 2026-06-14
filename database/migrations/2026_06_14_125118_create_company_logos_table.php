<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('company_logos', function (Blueprint $table) {
      $table->id();
      $table->foreignId('job_listing_id')->constrained('job_listings')->onDelete('cascade');
      $table->string('original_name');
      $table->string('logo_path');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('company_logos');
  }
};
