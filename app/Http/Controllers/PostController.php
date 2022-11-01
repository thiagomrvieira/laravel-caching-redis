<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class PostController extends Controller
{
    public function index() {

        $cachedPosts = Redis::get('posts');
        if(isset($cachedPosts)) {
            $posts = json_decode($cachedPosts, FALSE);
      
            return response()->json([
                'status_code' => 201,
                'message'     => 'Fetched from redis',
                'data'        => $posts,
            ]);
        }else {
            $posts = Post::all();

            Redis::set('posts', $posts);
      
            return response()->json([
                'status_code' => 201,
                'message'     => 'Fetched from database',
                'data'        => $posts,
            ]);
        }
      }

    public function show($id) {

        $cachedPost = Redis::get('blog_' . $id);
        if(isset($cachedPost)) {
            $post = json_decode($cachedPost, FALSE);
      
            return response()->json([
                'status_code' => 201,
                'message' => 'Fetched from redis',
                'data' => $post,
            ]);
        }else {
            $post = Post::find($id);
            Redis::set('blog_' . $id, $post);
      
            return response()->json([
                'status_code' => 201,
                'message'     => 'Fetched from database',
                'data'        => $post,
            ]);
        }
    }

    public function update(Request $request, $id) {

        $update = Post::findOrFail($id)->update($request->all());
      
        if($update) {
      
            // Delete blog_$id from Redis
            Redis::del('post_' . $id);
      
            $post = Post::find($id);
            
            // Set a new key with the post id
            Redis::set('post_' . $id, $post);
      
            return response()->json([
                'status_code' => 201,
                'message'     => 'Post updated',
                'data'        => $post,
            ]);
        }
      
    }

    public function delete($id) {

        Post::findOrFail($id)->delete();

        Redis::del('post_' . $id);
      
        return response()->json([
            'status_code' => 201,
            'message'     => 'Post deleted'
        ]);
    }
}
