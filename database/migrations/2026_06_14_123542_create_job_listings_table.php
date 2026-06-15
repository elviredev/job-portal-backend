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
    Schema::create('job_listings', function (Blueprint $table) {
      $table->id();
      $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

      // Job Details
      $table->string('title', 255);
      $table->string('location', 100);
      $table->enum('location_type', ['remote', 'on_site', 'hybrid']);
      $table->decimal('min_salary', 10, 2);
      $table->decimal('max_salary', 10, 2)->nullable();
      $table->enum('job_type', ['full-time', 'part-time', 'contract', 'internship', 'freelance']);
      $table->enum('level', ['intern', 'junior', 'mid', 'senior', 'lead', 'manager']);
      $table->date('application_deadline');
      $table->timestamp('posted_date')->useCurrent();

      // Company info
      $table->string('company_name', 255);
      $table->text('company_description')->nullable();
      $table->string('contact_person', 200);
      $table->string('company_email', 255);
      $table->string('department', 100);
      $table->string('website', 255)->nullable();

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('job_listings');
  }
};
