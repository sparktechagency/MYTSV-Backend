<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
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
            'category_id' => 'required|numeric|exists:categories,id',
            'type'        => 'required|string|in:video,link',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail'   => 'required|mimes:png,jpg,jpeg|max:204800',
            'video'       => 'required_if:type,video|mimes:mp4|max:51200',
            'link'        => 'required_if:type,link|url',
            'states'      => 'required|string',
            'city'        => 'required|string',
            'tags'        => 'required',
            'is_promoted' => 'required|in:1,0',
            'visibility'  => 'required|in:Everyone,Only me',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        $video              = new Video();
        $video->user_id     = Auth::user()->id;
        $video->category_id = $request->category_id;
        $video->type        = $request->type;
        $video->title       = $request->title;
        $video->description = $request->description;
        if ($request->hasFile('thumbnail')) {
            $thumbnail  = $request->file('thumbnail');
            $final_name = time() . '.' . $thumbnail->getClientOriginalExtension();
            $thumbnail->move(public_path('uploads/thumbnail'), $final_name);
            $video->thumbnail = $final_name;
        }
        if ($request->type == 'video' && $request->hasFile('video')) {
            $uploadedVideo = $request->file('video');
            $final_name    = time() . '.' . $uploadedVideo->getClientOriginalExtension();
            $uploadedVideo->move(public_path('uploads/video'), $final_name);
            $video->video = $final_name;
        }

        if ($request->type == 'link') {
            $video->link = $request->link;
        }
        $video->states      = $request->states;
        $video->city        = $request->city;
        $video->tags        = $request->tags;
        $video->is_promoted = $request->is_promoted;
        $video->visibility  = $request->visibility;
        $video->save();
        return response()->json([
            'status'  => true,
            'message' => 'Video created successfully.',
            'data'    => $video,
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
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|numeric|exists:categories,id',
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'thumbnail'   => 'sometimes|mimes:png,jpg,jpeg|max:204800',
            'video'       => 'sometimes|mimes:mp4|max:51200',
            'link'        => 'sometimes|url',
            'states'      => 'required|string',
            'city'        => 'required|string',
            'tags'        => 'required',
            'visibility'  => 'required|in:Everyone,Only me',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        try {
            $video              = Video::findOrFail($id);
            $video->user_id     = Auth::user()->id;
            $video->category_id = $request->category_id;
            $video->title       = $request->title;
            $video->description = $request->description;
            // Thumbnail upload
            if ($request->hasFile('thumbnail')) {
                $photo_location     = public_path('uploads/thumbnail');
                $old_photo          = basename($video->thumbnail);
                $old_photo_location = $photo_location . '/' . $old_photo;
                if (file_exists($old_photo_location)) {
                    unlink($old_photo_location);
                }
                $file = $request->file('thumbnail');
                $name = time() . '.' . $file->extension();
                $file->move(public_path('uploads/thumbnail'), $name);
                $video->thumbnail = $name;
            }

            // Video upload
            if ($request->hasFile('video')) {
                $video_location     = public_path('uploads/video');
                $old_video          = basename($video->video);
                $old_video_location = $video_location . '/' . $old_video;
                if (file_exists($old_video_location)) {
                    unlink($old_video_location);
                }

                $file = $request->file('video');
                $name = time() . '.' . $file->extension();
                $file->move(public_path('uploads/video'), $name);
                $video->video = $name;
            }
            $video->link       = $request->link;
            $video->states     = $request->states;
            $video->city       = $request->city;
            $video->tags       = $request->tags;
            $video->visibility = $request->visibility;
            $video->save();

            return response()->json([
                'status'  => true,
                'message' => 'Video update successfully.',
                'data'    => $video,
            ], 200);
        } catch (Exception $e) {
            Log::error('Video update error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'data not found',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $video = Video::findOrFail($id);
            if ($video->thumbnail) {
                $photo_location     = public_path('uploads/thumbnail');
                $old_photo          = basename($video->thumbnail);
                $old_photo_location = $photo_location . '/' . $old_photo;
                if (file_exists($old_photo_location)) {
                    unlink($old_photo_location);
                }
            }
            if ($video->video) {
                $video_location     = public_path('uploads/video');
                $old_video          = basename($video->video);
                $old_video_location = $video_location . '/' . $old_video;
                if (file_exists($old_video_location)) {
                    unlink($old_video_location);
                }
            }
            $video->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Video deleted successfully.',
                'data'    => $video,
            ], 200);
        } catch (Exception $e) {
            Log::error('Video deleted error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'data not found',
            ]);
        }
    }

    public function bulkDelete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:videos,id',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        $videos = Video::whereIn('id', $request->ids)->get();
        foreach ($videos as $video) {
            if ($video->thumbnail) {
                $photo_location     = public_path('uploads/thumbnail');
                $old_photo          = basename($video->thumbnail);
                $old_photo_location = $photo_location . '/' . $old_photo;
                if (file_exists($old_photo_location)) {
                    unlink($old_photo_location);
                }
            }
            if ($video->video) {
                $video_location     = public_path('uploads/video');
                $old_video          = basename($video->video);
                $old_video_location = $video_location . '/' . $old_video;
                if (file_exists($old_video_location)) {
                    unlink($old_video_location);
                }
            }
            $video->delete();
        }

        return response()->json([
            'status'  => true,
            'message' => 'Videos deleted successfully.',
        ]);
    }

    public function changeVisibility(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'visibility' => 'required|in:Everyone,Only me',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        try {
            $video             = Video::findOrFail($id);
            $video->visibility = $request->visibility;
            $video->save();
            return response()->json([
                'status'  => true,
                'message' => 'Video visibility updated successfully.',
                'data'    => $video,
            ], 200);
        } catch (Exception $e) {
            Log::error('Video visibility update error: ' . $e->getMessage());
            return response()->json([
                'status'  => false,
                'message' => 'data not found',
            ]);
        }
    }
}
