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
		$permissions = $this->user_permissions()->get();
		return [
			'id' => $this->id,
			'name' => $this->name,
			'groups' => GroupResource::collection($this->groups()->get()),
            'userPermissions' => $permissions->count() > 0 ? $permissions->mapWithKeys(function (User $user, int $key) {
                return [$user->id => $user->pivot->crudx];
            }) : null,
            'extension' => $this->when($this->extension, $this->extension),
		];
	}
}
