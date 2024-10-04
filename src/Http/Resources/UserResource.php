<?php

namespace Botble\Api\Http\Resources;

use Botble\ACL\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
        ];

        if ($this->first_name || $this->last_name) {
            $data['first_name'] = $this->first_name;
            $data['last_name'] = $this->last_name;
        }

        return [
            ...$data,
            'email' => $this->email,
            'name' => $this->name,
            'phone' => $this->phone,
            'avatar' => $this->avatar_url,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'description' => $this->description,
        ];
    }
}
