<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\Models\Directory;
use App\Models\File;
use App\Models\Node;
use App\Models\User;

use App\Http\Requests\NodeCreateRequest;

use App\Http\Resources\MountDirectoryResource;
use App\Http\Resources\NodeResource;

class DiskController extends Controller
{
    public function createNode(NodeCreateRequest $request, Directory $node)
	{
        $params = $request->input();
        $is_file = isset($params['data']) && isset($params['extension']) ? true : false;

		$user = $request->user();
        $userGroups = $user->groups()->get()->pluck('id')->toArray();
        $userPerms = $node->current_user_permissions()->first();

        // Our unassigned node
        $newNode = $is_file ? new File() : new Directory();
        $newNode->name = $params['name'];

        if ($is_file) {
            $newNode->extension = $params['extension'];
            // TODO: actually save data
            $newNode->data = $params['data'];
        }

        $newNode->parent_id = $node->id;

        // Create new node
        if ($newNode->save()) {

            // Assign groups of current user
            if (!empty($userGroups)) $newNode->groups()->attach($userGroups);
            // If directory has user perms, also assign that to new node
            if ($userPerms != null) $newNode->user_permissions()->attach($user->id, ['crudx' => $userPerms->pivot->crudx]);

            // Alert frontend user that the node is created
            return response()->json([
                'message' => ucfirst($newNode->nodeType) . " $newNode->name was successfully created."
            ], 200);

        }

        // Otherwise there was an error
        return response()->json([
            'message' => $node->errorMessage
        ], $node->errorCode);
	}

	public function readFile(Request $request, File $node)
	{
        return response()->json([
            'message' => 'File data will be implemented at a later date.'
        ], 200);
	}

	public function readDirectory(Directory $node)
	{
        return response()->json(new MountDirectoryResource($node), 200);
	}

    public function updateNode(Request $request, Node $node, string $permissions, int $user_id = null) {
        $affectedUser = $user_id == null ? $request->user() : User::findOrFail($user_id);

        // Detach if one is available
        $node->user_permissions()->detach($affectedUser->id);
        $node->user_permissions()->attach($affectedUser->id, ['crudx' => $permissions]);

        return response()->json([
            'message' => "Permissions for $affectedUser->name have been updated for $node->name as $permissions."
        ], 200);
    }

    public function deleteNode(Node $node) {
        if ($node->delete()) {
            return response()->json([
                'message' => "$node->name was successfully deleted."
            ], 200);
        }

        return response()->json([
            'message' => $node->errorMessage
        ], $node->errorCode);
    }

    public function executeFile(File $node)
	{
        return response()->json([
            'message' => 'File execution will be implemented at a later date.'
        ], 200);
	}

    public function mount()
	{
        $rootNode = Directory::where('name', '=', '')->first();

        return response()->json(["node" => new NodeResource($rootNode)], 200);
	}

    public function moveNode(Directory $destination, Node $child)
    {
        // Update parent location
        $child->parent_id = $destination->id;

        // If child saves, storage was moved successfully
        if ($child->save()) {
            return response()->json([
                'message' => "The $child->nodeType " . $child->get_name() ." was successfully moved!"
            ], 200);
        }

        // Otherwise an error occurred, probably already existed
        return response()->json([
            'message' => $child->errorMessage
        ], $child->errorCode);
    }
}
