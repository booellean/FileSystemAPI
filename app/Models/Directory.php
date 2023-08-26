<?php

namespace App\Models;

use App\Models\Node as NodeModel;

use Illuminate\Support\Facades\Storage;

class Directory extends NodeModel
{
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

    public function read_node()
    {
        // if ($this->is_directory()) {

        // } else {

        // }
        // Storage::disk('root')
        // return self::where('name', '=', $parent_name)->first();
    }
}
