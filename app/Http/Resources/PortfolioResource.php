<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Models\Portfolio;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>

     */

    
    public function toArray(Request $request): array
    {
        return [
        	"id" => $this->id,
        	"title" => $this->title,
        	"description" => $this->description,
        	"image_url" => $this->image_url,
        	"link_projects" => $this->link_projects
        ];
    }
}
