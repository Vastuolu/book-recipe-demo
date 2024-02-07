<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FavoriteFoodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = $this->recipe->image_filename ? $this->getPresignedUrl($this->recipe->image_filename) : null;

        return [
            'recipeId' => $this->recipe->recipe_id,
            'categories' => [
                'category_id' => $this->recipe->category->category_id,
                'category_name' => $this->recipe->category->category_name,
            ],
            'levels'=>[
                'level_id' => $this->recipe->level->level_id,
                'level_name' => $this->recipe->level->level_name,
            ],
            'recipeName' => $this->recipe->recipe_name,
            'imageUrl' => $imageUrl,
            'imageFileName' => $this->recipe->image_filename,
            'time' => $this->recipe->time_cook,
            'isFavorite' => $this->is_favorite,
        ];
    }

    private function getPresignedUrl($filename):?string{
        try {
            $url = Storage::temporaryUrl($filename, now()->addMinutes(15));
            return $url;
        } catch (\Throwable $error) {
            Log::error($error);
            return null;
        }
    }
}
