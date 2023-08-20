<?php

namespace App\Http\Controllers;

use App\Models\Node;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use Illuminate\Support\Facades\Storage;

class NodeController extends Controller
{
    public function createNode(Node $node)
	{
        return "can create";
	}

	public function readNode(Request $request, Node $node)
	{
        return "can read";
	}

    public function updateNode(Node $node)
	{
        return "can update";
	}

    public function deleteNode(Node $node)
	{
        return "can delete";
	}

    public function executeNode(Node $node)
	{
        return "can execute";
	}
}
