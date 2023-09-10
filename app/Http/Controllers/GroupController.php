<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

use App\Models\Group;

use App\Http\Resources\GroupResource;

class GroupController extends Controller
{
    public function getGroups()
    {
        return response()->json(GroupResource::collection(Group::all()), 200);
    }
}
