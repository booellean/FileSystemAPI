<?php

namespace App\Models;

use App\Models\Node as NodeModel;

use Illuminate\Support\Facades\Storage;

class File extends NodeModel
{
    /**
     * Custom property for node typing
     * @var string
     */
    public $nodeType = 'file';

    /**
     * Custom property for held data.
     * Used for saving new nodes
     * @var string
     */
    public $data = '';

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
        throw new BadMethodCallException('Files cannot have children and thus are never empty.');
    }

    public function get_name(): string
    {
        return $this->name . '.' . $this->extension;
    }

    protected function put_in_storage(): bool
    {
        return Storage::disk($this->disk)->put($this->get_path_name(), $this->data);
    }

    protected function delete_from_storage(): bool
    {
        if (Storage::disk($this->disk)->delete($this->get_path_name())) return true;

        $this->errorMessage = 'The file could not be deleted.';
        $this->errorCode = 502;

        return false;
    }
}
