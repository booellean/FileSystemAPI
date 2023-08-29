<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Storage;

use App\Models\Directory;
use App\Models\File;
use App\Models\Node;
use App\Models\User;

use App\Http\Requests\NodeCreateRequest;

use App\Http\Resources\MountDirectoryResource;
use App\Http\Resources\NodeResource;

class StorageController extends Controller
{
    public function createNode(NodeCreateRequest $request, Directory $node)
	{
        $params = $request->input();
        $path_name = $node->name . '/' . $params['name'];

		$user = $request->user();
        $userGroups = $user->groups()->get()->pluck('id')->toArray();
        $userPerms = $node->current_user_permissions()->first();

        // Our unassigned node
        $newNode;

        try {
            // Check if this is a file with data attached
            if (isset($params['data'])) {
                $newNode = new File();
                Storage::disk('root')->put($path_name, $params['data']);
            } else {
                $newNode = new Directory();
                Storage::disk('root')->makeDirectory($path_name);
            }

            // Create new node
            $newNode->name = $path_name;
            $newNode->save();

            // Assign groups of current user
            if (!empty($userGroups)) $newNode->groups()->attach($userGroups);
            // If directory has user perms, also assign that to new node
            if ($userPerms != null) $newNode->user_permissions()->attach($user->id, ['crudx' => $userPerms->pivot->crudx]);

            // Alert frontend user that the node is created
            return response()->json([
                'message' => "$newNode->name was successfully created."
            ], 200);

        } catch(\Exception $error) {
            return response()->json([
                'message' => "$error" // for debugging
                // 'message' => "An unknown error occurred."
            ], 500);
        }
	}

	public function readFile(Request $request, File $node)
	{
        return response()->json([
            'message' => 'File data will be implemented at a later date.'
        ], 200);
	}

	public function readDirectory(Request $request, Directory $node)
	{
        return response()->json(new MountDirectoryResource($node), 200);
	}

    public function updateFile(Request $request, File $node, string $permissions, int $user_id = null)
	{
        return $this->updateNode($request, $node, $permissions, $user_id);
	}

    public function updateDirectory(Request $request, Directory $node, string $permissions, int $user_id = null)
	{
        return $this->updateNode($request, $node, $permissions, $user_id);
	}

    private function updateNode(Request $request, Node $node, string $permissions, int $user_id) {
        $affectedUser = $user_id == null ? $request->user() : User::findOrFail($user_id);

        // Detach if one is available
        $node->user_permissions()->detach($affectedUser->id);
        $node->user_permissions()->attach($affectedUser->id, ['crudx' => $permissions]);

        return response()->json([
            'message' => "Permissions for $affectedUser->name have been updated for $node->name as $permissions."
        ], 200);
    }

    public function deleteFile(File $node)
	{
        return $this->deleteNode($node);
	}

    public function deleteDirectory(Directory $node)
	{
        return $this->deleteNode($node);
	}

    private function deleteNode(Node $node) {
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

    public function mount(Request $request)
	{
        $rootNode = Directory::where('name', '=', '')->first();

        return response()->json(["node" => new NodeResource($rootNode)], 200);
	}

    public function moveDirectory(Directory $destination, Directory $child)
    {
        return $this->moveNode($destination, $child);
    }

    public function moveFile(Directory $destination, File $child)
    {
        return $this->moveNode($destination, $child);
    }

    private function moveNode(Directory $destination, Node $child)
    {
        $nodeName = $child->get_item_name();
        $newChildName = $destination->name . '/' . $nodeName;

        if ($child->already_exists($newChildName)) {
            return response()->json([
                'message' => "There's already a $child->nodeType named $nodeName at $destination->name. Please rename the $child->nodeType first."
            ], 405);
        }

        // TODO: directory children and parents have to be changed as well
        if (Storage::disk('root')->move($child->name, $newChildName)) {
            $child->name = $newChildName;
            $child->save();

            return response()->json([
                'message' => "The $child->nodeType $child->name was successfully moved!"
            ], 200);
        }

        return response()->json([
            'message' => "An unknown error occurred. The $child->nodeType could not be moved."
        ], 500);
    }
}
