<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Models\Directory;
use App\Models\File;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

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
        $filesAndDirectories = $this->get_all_directory_children();

		return [
			'directories' => NodeResource::collection($filesAndDirectories['directories']),
            'files' => NodeResource::collection($filesAndDirectories['files'])
		];
	}

    private function get_all_directory_children()
    {
        $files = $this->map_children_names(Storage::disk('root')->files($this->name));
        $directories = $this->map_children_names(Storage::disk('root')->directories($this->name));

        return [
            'files' => File::whereIn('name', $files)->get(),
            'directories' => Directory::whereIn('name', $directories)->get(),
        ];
    }

    private function map_children_names($array)
    {
        return Arr::map($array, function (string $child_name) {
            return $this->name . '/' . $child_name;
        });
    }
}
