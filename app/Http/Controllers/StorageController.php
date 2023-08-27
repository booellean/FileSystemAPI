<?php

namespace App\Http\Controllers;

use App\Models\Directory;
use App\Models\File;
use App\Models\Node;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Storage;

use App\Http\Requests\NodeCreateRequest;

class StorageController extends Controller
{
    public function createNode(Request $request, Directory $node)
	{
        // Get directory otherwise

        // Assign groups of parent directory
        // If directory has user perms, also assign that to new node

        // Is it a file???
        return "can create";
	}

	public function readFile(Request $request, File $node)
	{
        return "can read file";
	}

	public function readDirectory(Request $request, Directory $node)
	{
        return "can read directory";
	}

    public function updateFile(File $node)
	{
        return "can update";
	}

    public function updateDirectory(Directory $node)
	{
        return "can update";
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
        return "can execute";
	}
}
