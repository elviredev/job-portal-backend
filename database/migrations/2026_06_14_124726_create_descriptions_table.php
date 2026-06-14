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
    Schema::create('descriptions', function (Blueprint $table) {
      $table->id();
      $table->foreignId('job_listing_id')->constrained('job_listings')->onDelete('cascade');
      $table->text('key_role');
      $table->text('responsibility');
      $table->text('skill_and_experience');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('descriptions');
  }
};
