<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EditInboxResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
        	'id' => $this->id,
        	'name' => $this->name,
        	'company' => $this->company,
        	'status' => $this->status
        ];
    }
}
