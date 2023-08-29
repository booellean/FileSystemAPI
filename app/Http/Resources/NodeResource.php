<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Models\User;

class NodeResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
            'extension' => $this->when($this->extension, $this->extension),
			'groups' => $this->groups()->get()->pluck('id')->toArray(),
            'user_permissions' => $this->user_permissions()->get()->mapWithKeys(function (User $user, int $key) {
                return [$user->id => $user->pivot->crudx];
            })
		];
	}
}
