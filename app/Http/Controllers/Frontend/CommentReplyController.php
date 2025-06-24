<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CommentReply;
use App\Models\CommentReplyReaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment_id' => 'required|numeric|exists:comments,id',
            'reply'      => 'required|string',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        $reply             = new CommentReply();
        $reply->user_id    = Auth::user()->id;
        $reply->comment_id = $request->comment_id;
        $reply->reply      = $request->reply;
        $reply->save();
        return response()->json([
            'status'  => true,
            'message' => 'Comment reply created successfully.',
            'data'    => $reply,
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function addOrRemoveReplyReaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reply_id' => 'required|numeric|exists:comment_replies,id',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        $reply_reaction = CommentReplyReaction::where('user_id', Auth::user()->id)->where('comment_reply_id', $request->reply_id)->first();
        if ($reply_reaction) {
            $reply_reaction->delete();
            $status = 'Comment reply reaction removed';
        } else {
            $reply_reaction = CommentReplyReaction::create([
                'user_id'          => Auth::id(),
                'comment_reply_id' => $request->reply_id,
            ]);
            $status = 'Comment reply reaction added';
        }
        return response()->json([
            'status'  => true,
            'message' => $status,
        ], 200);
    }
}
