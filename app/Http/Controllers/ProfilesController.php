<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;

class ProfilesController extends Controller
{
    public function getRandom(\App\User $user)
    {
        return $user::all()->random()->id;
        //User::all()->random()->user_id
    }

    public function index(\App\User $user)
    {
        $follows = (auth()->user()) ? auth()->user()->following->contains($user->id) : false;

        $postCount = Cache::remember(
            'count.posts.' . $user->id,
            now()->addSeconds(30),
            function () use ($user) {
                return $user->posts->count();
            });

        $followingCount = Cache::remember(
            'count.posts.' . $user->id,
            now()->addSeconds(30),
            function () use ($user) {
                return $user->following->count();
            });

        $followersCount = Cache::remember(
            'count.posts.' . $user->id,
            now()->addSeconds(30),
            function () use ($user) {
                return $user->profile->followers->count();
            });

        return view('profiles.index', compact('user', 'follows', 'postCount', 'followingCount', 'followersCount'));
    }

    public function edit(\App\User $user)
    {
        $this->authorize('update', $user->profile);


        return view('profiles.edit', compact('user'));
    }

    public function update(\App\User $user)
    {
        $this->authorize('update', $user->profile);


        $data = request()->validate([
            'title' => 'required',
            'description' => '',
            'image' => '',
        ]);


        if (request('image')) {
            $filename = request()->file('image')->getClientOriginalName();
            $image = request()->file('image');
            $image->move('img', $filename);
            $imageArray = ['image' => $filename];
            
            //$imagePath = request('image')->store('profiles', 'public');

            /*
            $image = Image::make(public_path("storage/{$imagePath}"))->fit(1000, 1000);
            $image->save();

            $imageArray = ['image' => $imagePath];*/
        }

        auth()->user()->profile()->update(array_merge(
            $data,
            $imageArray ?? [],
        ));

        return redirect("/profile/{$user->id}");
    }
}
