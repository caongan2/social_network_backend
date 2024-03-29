<?php

namespace App\Http\Controllers;



use App\Http\Services\PostService;
use App\Models\Comment;
use App\Models\Friend;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class PostController extends Controller
{


    /**
     * @var PostService
     */
    public $postService;

    public function __construct(PostService $service)
    {
        $this->middleware("auth:api");
        $this->postService = $service;
    }

    public function index()
    {
        $posts = $this->postService->getAll();
        return response()->json($posts);
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'userId'=>'required',
            'content'=>'required',
            'is_public'=>'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }
        $post = $this->postService->create($request->all());
        $data = [
            "message" => "create post success",
            "data" => $post,
        ];
        return response()->json($data);

    }

    public function countData()
    {
        $users = User::all()->count();
        $posts = Post::all()->count();
        $user_onl = $users * 0.7;
        
        return response()->json([
            'user' => $users,
            'post' => $posts,
            'online' => $user_onl
        ]);
    }

    public function update(Request $request,$id)
    {
        $validator = Validator::make($request->all(),[
            'content'=>'required',
            'is_public'=>'required'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),422);
        }
        $post = $this->postService->update($request->all(),$id);
        $data = [
            "message" => "update post success",
            "data" => $post,
        ];
        return response()->json($data);
    }


    public function delete($id)
    {
        $this->postService->destroy($id);
        return response()->json('Delete Success');
    }

    public function getPostByUser($id)
    {
        return $this->postService->getPostByUser($id);
    }

    public function showPost($id)
    {
        return $this->postService->findById($id);
    }

    public function likePost($id)
    {
        $likePost = Like::where('post_id',$id)->where('user_id',Auth::id())->first();
        if ($likePost){
            $likePost->delete();
        }else{
            $like = new Like();
            $like->user_id = Auth::id();
            $like->post_id = $id;
            $like->is_status = true;
            $like->save();

        }
        $countLike = Like::where('post_id',$id)->count();
        return response()->json($countLike);
    }
    public function countLikeByPost($id)
    {
        $like = Like::where('post_id',$id)->get();
        return response()->json($like);
    }

    public function getRelationShip($id)
    {
        $friend = DB::table('friends')
                    ->select('is_accept')
                    ->where('friend_id',$id)
                    ->get();
        return response()->json($friend);
    }

}
