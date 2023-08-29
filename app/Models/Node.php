<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

use App\Models\Directory;
use App\Models\Group;
use App\Models\User;

abstract class Node extends Model
{
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
            if ($node->delete_from_storage()) {
                $node->groups()->detach($node->groups);
                $node->user_permissions()->detach($node->user_permissions);

                return true;
            }

            return false;
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

    public function get_item_name(): string
    {
        $directoryParts = explode('/', $this->name);
        return array_pop($directoryParts);
    }

    public function get_parent()
    {
        // TODO: Do we want the root parent to be the parent?
        // Or throw an exception?
        // if ($this->name == '') return null;

        $directoryParts = explode('/', $this->name);
        array_pop($directoryParts);
        $parent_name = implode('/', $directoryParts);

        return Directory::where('name', '=', $parent_name)->first();
    }

    public function already_exists(string $location): bool
    {
        return !!self::where('name', '=', $location)->first();
    }

    abstract public function is_empty(): bool;

    abstract protected function delete_from_storage(): bool;

}
