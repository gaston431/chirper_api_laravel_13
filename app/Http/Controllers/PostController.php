<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PostResource::collection(
            Post::with('user')->latest()
            ->take(50)  // Limit to 50 most recent chirps
            ->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //  var_dump(auth()->user()['id'], Auth::id());die;
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:posts',
            'content' => 'required|string',
        ]);

        // $post = Post::create(array_merge($validated, ['user_id' => Auth::id()]));
        // $post = Post::create([
        //     'title' => $validated['title'],
        //     'slug' => $validated['slug'],
        //     'content' => $validated['content'],
        //     'user_id' => $request->user()->id
        // ]);

        $post = $request->user()->posts()->create($validated);

        return response()->json($post->load('user')->toResource(), 201);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return new PostResource($post->load('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        Gate::authorize('update', $post);
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:posts',
            'content' => 'required|string',
        ]);

        // Update
        $post->update($validated);
        return response()->json($post->load('user')->toResource(), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        Gate::authorize('update', $post);
        $post->delete();
        return response()->json(null, 204); 
    }
}
