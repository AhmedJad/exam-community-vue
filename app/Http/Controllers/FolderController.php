<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\UpdateFolderRequest;
use App\Models\Folder;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class FolderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //Dashboard endpoints
    public function store(StoreFolderRequest $request)
    {
        $userId = JWTAuth::parseToken()->getPayload()->get("sub");
        $input = $request->validated();
        $input["user_id"] = $userId;
        return Folder::create($input);
    }

    public function update(UpdateFolderRequest $request)
    {
        $folder = Folder::find($request->id);
        $folder->update($request->validated());
        return $folder;
    }

    public function delete($id)
    {
        $folder = Folder::find($id);
        $folder->delete();
    }

    public function index()
    {
        $userId = JWTAuth::parseToken()->getPayload()->get("sub");
        $text = isset(request()->text) ? request()->text : '';
        if (request()->page_size) {
            return Folder::where("name", "like", "%$text%")->with("parent")->orderBy("id", "desc")
                ->when(request()->parent_id, function ($q) {
                    $q->where("parent_id", request()->parent_id);
                }, function ($q) {
                    $q->whereNull("parent_id");
                })
                ->where("user_id", $userId)
                ->paginate(request()->page_size);
        }
        return Folder::get();
    }
}
