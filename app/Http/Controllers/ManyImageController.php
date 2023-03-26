<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddImageRequest;
use App\Http\Requests\CreateManyImageRequest;
use App\Models\ManyImage;
use Illuminate\Support\Facades\Storage;

class ManyImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    //Dashboard endpoints
    public function store(CreateManyImageRequest $request)
    {
        $images = [];
        foreach ($request->file("images") as $file) {
            $images[] = $file->store("");
        }
        $manyImage = ManyImage::create(["images" => $images]);
        return $manyImage;
    }

    public function delete($id)
    {
        $oldImages = $this->_delete($id);
        foreach ($oldImages as $oldImage) {
            Storage::delete($oldImage);
        }
    }

    public function index()
    {
        if (request()->page_size) {
            return ManyImage::orderBy("id","desc")->paginate(request()->page_size);
        }
        return ManyImage::get();
    }

    public function deleteImage()
    {
        $manyImage = ManyImage::find(request()->id);
        $newImages = $manyImage->images;
        array_splice($newImages, request()->image_index, 1);
        $manyImage->images = $newImages;
        $manyImage->save();
        Storage::delete(request()->image);
        return ["many_image" => $manyImage];
    }

    public function addImage(AddImageRequest $request)
    {
        $image = request()->file("image")->store("");
        $manyImage = ManyImage::find($request->id);
        $newImages = $manyImage->images;
        $newImages[] = $image;
        $manyImage->images = $newImages;
        $manyImage->save();
        return ["many_image" => $manyImage];
    }

    //Commons    
    public function _delete($id)
    {
        $manyImage = ManyImage::find($id);
        $oldImages = [];
        if ($manyImage) {
            $oldImages = $manyImage->images;
            $manyImage->delete();
        }
        return $oldImages;
    }
}
