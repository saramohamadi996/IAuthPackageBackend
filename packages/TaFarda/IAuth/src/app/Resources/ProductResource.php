<?php

namespace TaFarda\IAuth\app\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'sms_verify_template' => $this->sms_verify_template,
            'email_verify_template' => $this->email_verify_template,
            'status' => intval($this->status),
            'admin' => new AdminResource($this->admin),
        ];
    }
}
