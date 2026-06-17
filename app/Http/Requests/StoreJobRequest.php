<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreJobRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, ValidationRule|array|string>
   */
  public function rules(): array
  {
    return [
      'title' => 'required|string|max:255',
      'department' => 'required|string|max:100',
      'level' => 'required|in:intern,junior,mid,senior,lead,manager',
      'location' => 'required|string|max:100',
      'location_type' => 'required|in:remote,on-site,hybrid',
      'job_type' => 'required|in:full-time,part-time,contract,internship,freelance',
      'application_deadline' => 'nullable|date',
      'min_salary' => 'required|numeric|min:0',
      'max_salary' => 'required|numeric|min:0|gt:min_salary',
      'company_name' => 'required|string|max:255',
      'website' => 'nullable|url|max:2048',
      'contact_person' => 'required|string|max:200',
      'company_email' => 'required|email|max:255',
      'company_description' => 'nullable|string',

      // descriptions
      'key_role' => 'required|string',
      'responsability' => 'required|string',
      'skill_and_experience' => 'required|string',

      // company logo
      'company_logo' => 'nullable|file|image|mimes:jpeg,png,jpg,webp,avif|max:4048',
    ];
  }
}
