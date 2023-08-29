<?php

namespace App\Models;

use App\Models\File;
use App\Models\Node as NodeModel;

use Illuminate\Support\Facades\Storage;

class Directory extends NodeModel
{
    /**
     * Custom property for node typing
     * @var string
     */
    public $nodeType = 'directory';

	/**
	 * The table of the model
	 * @var string
	 */
	protected $table = 'directories';

    /**
	 * --------------------------------------------------------------------------
	 *  Accessors
	 * --------------------------------------------------------------------------
	*/

    public function is_empty(): bool
    {
        return (empty((Storage::disk($this->disk)->files($this->get_path_name()))) && empty(Storage::disk($this->disk)->directories($this->get_path_name())));
    }

    public function get_name(): string
    {
        return $this->name;
    }

    protected function put_in_storage(): bool
    {
        return Storage::disk($this->disk)->makeDirectory($this->get_path_name());
    }

    protected function delete_from_storage(): bool
    {
        if ($this->is_empty()) {
            if (Storage::disk($this->disk)->deleteDirectory($this->get_path_name())) return true;

            $this->errorMessage = 'The directory could not be deleted.';
            $this->errorCode = 502;

            return false;
        }

        $this->errorMessage = 'The directory was not empty and could not be deleted.';
        $this->errorCode = 405;

        return false;
    }

    public function get_directories()
    {
        return self::where('parent_id', '=', $this->id)->get();
    }

    public function get_files()
    {
        return File::where('parent_id', '=', $this->id)->get();
    }

    public function update_location(string $destination)
    {
        // $this->name = $destination . '/' . $this->get_item_name();
        // $this->save();
    }

    private function update_children_names(string $destination)
    {

    }
}
