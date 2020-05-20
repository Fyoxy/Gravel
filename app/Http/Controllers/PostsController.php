<?php

namespace App\Http\Controllers;

use \App\Post;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;

class PostsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $users = auth()->user()->following()->pluck('profiles.user_id');
        $posts = Post::whereIn('user_id', $users)->with('user')->latest()->paginate(5);

        return view('posts.index', compact('posts'));
    }



    public function create()
    {
        return view('posts.create');
    }

    public function store()
    {
        $data = request()->validate([
            'caption' => 'required',
            'image' => 'required|image',
        ]);


        $filename = request()->file('image')->getClientOriginalName();
        $image = request()->file('image');
        $image->move('img', $filename);
        $imagePath = $filename;
        //$img = Image::make($image->getRealPath())->save('img');
        /*
        $imagePath = request('image')->store('img');
        //dd(request('image'));
        $image = Image::make($imagePath);//->fit(1200, 1200);
        $image->save();*/

        //$image = Image::make(request('image'))->fit(1200, 1200)->save('public/storage/uploads');

        auth()->user()->posts()->create([
            'caption' => $data['caption'],
            'image' => $imagePath,
        ]);

        return redirect('/profile/' . auth()->user()->id);
    }

    public function show(\App\Post $post)
    {
        return view('posts.show', compact('post'));
    }
}
