<?php

namespace App\Models;

use App\Models\Node as NodeModel;

use Illuminate\Support\Facades\Storage;

class File extends NodeModel
{
	/**
	 * The table of the model
	 * @var string
	 */
	protected $table = 'files';

    /**
	 * --------------------------------------------------------------------------
	 *  Accessors
	 * --------------------------------------------------------------------------
	*/

    public function is_empty(): bool
    {
        throw new BadMethodCallException('Files cannot have children and thus are never empty');
    }

    protected function delete_from_storage(): bool
    {
        if (Storage::disk('root')->delete($this->name)) return true;

        $this->errorMessage = 'The file could not be deleted.';
        $this->errorCode = 502;

        return false;
    }
}
