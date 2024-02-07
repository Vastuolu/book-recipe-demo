<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class RecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $isFavorite = false;

        if(isset($this->user_fav_foods)&&is_array($this->user_fav_foods)){
            $isFavorite = in_array($this->recipe_id, array_column($this->user_fav_foods, 'recipe_id'));
        }

        $imageUrl = $this->image_filename ? $this->getPresignedUrl($this->image_filename) : null;

        return [
            'recipeId' => $this->recipe_id,
            'categories' => [
                'category_id' => $this->category->category_id,
                'category_name' => $this->category->category_name,
            ],
            'levels'=>[
                'level_id' => $this->level->level_id,
                'level_name' => $this->level->level_name,
            ],
            'recipeName' => $this->recipe_name,
            'imageUrl' => $imageUrl,
            'imageFileName' => $this->image_filename,
            'time' => $this->time_cook,
            'isFavorite' => $isFavorite,
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
