<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHelloRequest;
use App\Http\Requests\UpdateHelloRequest;
use App\Models\Hello;
use Illuminate\Support\Facades\Storage;

class HelloController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //Dashboard endpoints
    public function store(StoreHelloRequest $request)
    {
        $image = $request->file("image")->store("");
        $request->merge(["image" => $image]);
        $employee = Hello::create($request->input());
        return $employee;
    }

    public function update(UpdateHelloRequest $request)
    {
        $image = "";
        if ($request->file("image")) {
            $image = $request->file("image")->store("");
            $request->merge(["image" => $image]);
        }
        $updateResult = $this->_update($request->input());
        if ($request->file("image")) {
            Storage::delete($updateResult["old_image"]);
        }
        return $updateResult["updated_hello"];
    }

    public function delete($id)
    {
        $oldImage = $this->_delete($id);
        if ($oldImage) {
            Storage::delete($oldImage);
        }
    }

    public function index()
    {
        $text = isset(request()->text) ? request()->text : '';
        if(request()->page_size){
            return Hello::whereRaw('LOWER(`title_ar`) LIKE ? or LOWER(`title_en`) LIKE ?', [
                "%" . strtolower($text) . '%',
                "%" . strtolower($text) . '%',
            ])->orderBy("id","desc")->paginate(request()->page_size);
        }
        return Hello::get();
    }
    //Commons    
    public function _update($helloInput)
    {
        $hello = Hello::find($helloInput["id"]);
        $oldImage = $hello->getImageName();
        $hello->title_ar = $helloInput["title_ar"];
        $hello->title_en = $helloInput["title_en"];
        $hello->list = $helloInput["list"];
        $hello->image = isset($helloInput["image"]) ? $helloInput["image"] : $oldImage;
        $hello->save();
        return ["old_image" => $oldImage, "updated_hello" => $hello];
    }
    public function _delete($id)
    {
        $hello = Hello::find($id);
        $oldImage = null;
        if ($hello) {
            $oldImage = $hello->getImageName();
            $hello->delete();
        }
        return $oldImage;
    }
}
