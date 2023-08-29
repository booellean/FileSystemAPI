<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Models\Directory;
use App\Models\File;

use App\Http\Resources\NodeResource;

class MountDirectoryResource extends JsonResource
{
	/**
	 * Transform the resource into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
        $files = $this->resource->get_files();
        $directories = $this->resource->get_directories();

		return [
			'directories' => NodeResource::collection($directories),
            'files' => NodeResource::collection($files)
		];
	}
}
