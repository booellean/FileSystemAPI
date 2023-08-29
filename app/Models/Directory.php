<?php

namespace App\Models;

use App\Models\Node as NodeModel;

use Illuminate\Support\Facades\Storage;

class Directory extends NodeModel
{
    /**
     * Custom node typing
     * @var int
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
        return (empty((Storage::disk('root')->files($this->name))) && empty(Storage::disk('root')->directories($this->name)));
    }

    protected function delete_from_storage(): bool
    {
        if ($this->is_empty()) {
            if (Storage::disk('root')->deleteDirectory($this->name)) return true;

            $this->errorMessage = 'The directory could not be deleted.';
            $this->errorCode = 502;

            return false;
        }

        $this->errorMessage = 'The directory was not empty and could not be deleted.';
        $this->errorCode = 405;

        return false;
    }
}
