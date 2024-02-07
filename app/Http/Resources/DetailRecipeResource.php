<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DetailRecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = $this->image_filename ? $this->getPresignedUrl($this->image_filename) : null;

        return [
            'recipeId' => $this->recipe_id,
            'categories' => [
                'categoryId' => $this->category->category_id,
                'categoryName' => $this->category->category_name,
            ],
            'levels'=>[
                'levelId' => $this->level->level_id,
                'levelName' => $this->level->level_name,
            ],
            'recipeName' => $this->recipe_name,
            'imageFilename' => $imageUrl,
            'timeCook' => $this->time_cook,
            'isFavorite' => $this->isFavorite,
            'ingridient' => $this->ingridient,
            'howToCook' => $this->how_to_cook
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
