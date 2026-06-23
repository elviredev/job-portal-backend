<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   * Renvoyer uniquement les champs utiles au front
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    $imagePath = $this->image?->image_path;

    return [
      'id' => $this->id,
      'first_name' => $this->first_name,
      'last_name' => $this->last_name,
      'full_name' => $this->full_name,
      'email' => $this->email,
      'role' => $this->role,
      'is_active' => $this->is_active,
      'image' => $imagePath
        ? (
        str_starts_with($imagePath, 'http') // google image
          ? $imagePath
          : asset('storage/' . $imagePath)
        )
        : null,
    ];
  }
}
