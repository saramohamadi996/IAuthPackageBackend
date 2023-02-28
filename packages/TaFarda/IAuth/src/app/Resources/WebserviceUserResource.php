<?php

namespace TaFarda\IAuth\app\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class WebserviceUserResource extends JsonResource
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
            'email' => $this->email,
            'mobile' => $this->mobile,
            'status' => intval($this->status),
            'profile' => [
                'first_name' => $this->profile->first_name,
                'last_name' => $this->profile->last_name,
                'father_name' => $this->profile->father_name,
                'sex' => $this->profile->sex,
                'birth_date' => $this->profile->birth_date,
                'national_code' => $this->profile->national_code,
                'identity_number' => $this->profile->identity_number,
                'address' => $this->profile->address,
                'phone' => $this->profile->phone,
                'postal_code' => $this->profile->postal_code,
                'state' => $this->profile->state,
                'city' => $this->profile->city,
            ],
        ];
    }
}
