<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Validator;
use App\Models\Blog;
use App\Http\Resources\Blog as BlogResource;

class BlogController extends BaseController
{

    public function index()
    {
        $blogs = Blog::all();
        return $this->sendResponse(BlogResource::collection($blogs), 'Posts fetched.');
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'file' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',

        ]);

        $path = $request->file('file')->store('public/images');

        $blog = new Blog;
        $blog->title = $request->title;
        $blog->description = $request->description;
        $blog->file = $path;
        $blog->save();

        return $this->sendResponse(new BlogResource($blog), 'Post created.');
    }


    public function show($id)
    {
        $blog = Blog::find($id);
        if (is_null($blog)) {
            return $this->sendError('Post does not exist.');
        }
        return $this->sendResponse(new BlogResource($blog), 'Post fetched.');
    }


    public function update(Request $request, Blog $blog)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required',
            'description' => 'required',
            'file' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors());
        }

        $blog->title = $input['title'];
        $blog->description = $input['description'];
        $blog->file = $input['file'];
        $blog->save();

        return $this->sendResponse(new BlogResource($blog), 'Post updated.');
    }

    public function destroy(Blog $blog)
    {
        $blog->delete();
        return $this->sendResponse([], 'Post deleted.');
    }

    public function imageUploadPost(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imageName = time() . '.' . $request->image->extension();

        $request->image->move(public_path('images'), $imageName);

        /* Store $imageName name in DATABASE from HERE */

        return back()
            ->with('success', 'You have successfully upload image.')
            ->with('image', $imageName);
    }
}
