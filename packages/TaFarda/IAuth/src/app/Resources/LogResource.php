<?php

namespace TaFarda\IAuth\app\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class LogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return
            [
                'id' => $this->id,
                'location' => $this->log_name,
                'description' => $this->description,
                'subject_type' => $this->subject_type,
                'causer' => $this->causer,
                'properties' => $this->properties,
            ];
    }


}
