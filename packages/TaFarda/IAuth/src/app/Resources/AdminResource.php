<?php

namespace TaFarda\IAuth\app\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminResource extends JsonResource
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
            'status' => intval($this->status),
            'webservice_call_token' => $this->webservice_call_token,
            'last_successful_login' => $this->last_successful_login ? Carbon::make($this->last_successful_login)->timestamp : null,
            'roles' => RoleResource::collection($this->roles),
            'permissions' => PermissionResource::collection($this->permissions),
        ];
    }
}
