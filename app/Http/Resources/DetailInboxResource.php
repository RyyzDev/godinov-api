<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class DetailInboxResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {


        //ternary if
        $kondisistatus = ($this->status === 0) ? "Belum diproses" : "Sudah Diproses";
        return [
            'id' => $this->id,
        	'name' => $this->name,
        	'email' => $this->email,
        	'contact'=> $this->contact,
        	'address' => $this->address,
        	'created_at' => date_format($this->created_at, "Y/m/d H:i:s"),
        	'description' => $this->description,
        	'company' => $this->company,
        	'status' => $kondisistatus
        ];
    }
}
