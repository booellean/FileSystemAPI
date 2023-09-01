<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

use App\Models\Directory;
use App\Models\Group;
use App\Models\User;

use Illuminate\Support\Facades\Storage;

abstract class Node extends Model
{
    /**
     * Custom property to indicate disk storage name
     * @var string
     */
    public $disk = 'root';

    /**
     * Custom property to help with error messages
     * @var string
     */
    public $errorMessage = 'An unknown error has occurred.';

    /**
     * Custom error status code
     * @var int
     */
    public $errorCode = 500;

    /**
     * Custom node typing
     * @var int
     */
    public $nodeType = 'node';

	/**
     * The name of the primary key,
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "with" property. Automatically loads these relationships on "get"
     * @var string[]
     */
    protected $with = ['current_user_permissions', 'groups'];

    /**
	 * The "boot" method.
	 *
	 * Watches the model's dispatched events.
	 *
	 */
    protected static function boot() {
        parent::boot();

        // This runs before deleting a file or directory
        static::deleting( function($node) {
            if (!$node->delete_from_storage()) return false;

            $node->groups()->detach($node->groups);
            $node->user_permissions()->detach($node->user_permissions);

            return true;
        });

        static::saving( function($node) {
            // If this is a brand new node
            if (!$node->id) return !$node->already_exists($node->parent_id) && $node->put_in_storage();

            return true;
        });

        static::updating( function($node) {
            return $node->check_for_parent_changes_and_update();
        });
    }

    /**
	 * --------------------------------------------------------------------------
	 *  Eloquent Model Relationships
	 * --------------------------------------------------------------------------
	*/

	/**
	 * The groups of the file or directory
	 */
	public function groups(): MorphToMany
	{
		return $this->morphToMany(Group::class, 'groupable');
	}

    /**
	 * The custom permissions set to users of this file or directory
	 */
	public function user_permissions(): MorphToMany
	{
		return $this->morphToMany(User::class, 'permissions')->withPivot('crudx');
	}

    /**
	 * The custom permissions set to users of this file or directory
	 */
	public function current_user_permissions()
	{
        $user = request()->user();
		return $this->user_permissions()->where('user_id', '=', ($user != null ? $user->id : '0'))->withPivot('crudx');
	}

    /**
	 * --------------------------------------------------------------------------
	 *  Accessors
	 * --------------------------------------------------------------------------
	*/

    public function get_parent()
    {
        return Directory::find($this->parent_id);
    }

    public function get_path_name(): string
    {
        $parent = $this->get_parent();
        $path_name = $this->get_name();

        while ($parent != null) {
            $path_name = $parent->name . '/' . $path_name;
            $parent = $parent->get_parent();
        }

        return $path_name;
    }

    public function already_exists(int $dest_parent_id): bool
    {
        return !!self::where([
            ['name', '=', $this->name],
            ['parent_id', '=', $dest_parent_id],
        ])->first();
    }

    protected function check_for_parent_changes_and_update(): bool
    {
        // ov = original values, nv = new values
        $ov = $this->getOriginal();
        $nv = $this->getAttributes();

        if ($ov['parent_id'] != $nv['parent_id']) {
            $oldDirectory = Directory::findOrFail($ov['parent_id']);
            $old_location = $oldDirectory->get_path_name() . '/' . $this->get_name();
            $new_location = $this->get_path_name();

            return Storage::disk($this->disk)->move($old_location, $new_location);
        }

        // Otherwise they are not changing storage location, return true
        return true;
    }

    abstract public function get_name(): string;

    abstract public function is_empty(): bool;

    abstract protected function delete_from_storage(): bool;

    abstract protected function put_in_storage(): bool;
}
