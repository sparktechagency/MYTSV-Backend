<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Number;

class HomeController extends Controller
{
    public function getPromotionalVideo(Request $request)
    {
        $promotional_videos = Video::with('user:id,channel_name,avatar')->select('id', 'user_id', 'category_id', 'thumbnail', 'title', 'is_promoted', 'states', 'city', 'views', 'created_at')->latest('id')->where('is_promoted', 1)->where('visibility', 'Everyone')->where('is_suspend', 0);
        if ($request->category_id) {
            $promotional_videos = $promotional_videos->where('category_id', $request->category_id);
        }
        if ($request->location) {
            $promotional_videos = $promotional_videos->where('states', 'LIKE', '%' . $request->location . '%')->orWhere('city', 'LIKE', '%' . $request->location . '%');
        }
        $promotional_videos = $promotional_videos->paginate($request->per_page ?? 10);

        $promotional_videos->getCollection()->transform(function ($promotional_video) {
            $promotional_video->views_count       = Number::abbreviate($promotional_video->views);
            $promotional_video->created_at_format = $promotional_video->created_at->diffForHumans();
            return $promotional_video;
        });
        return response()->json([
            'status'  => true,
            'message' => 'Promotional video retrieved successfully.',
            'data'    => $promotional_videos,
        ], 200);
    }

    public function getRelatedVideo(Request $request, $id)
    {
        $perPage    = $request->per_page ?? 10;
        $categoryId = Video::findOrFail($id)->category_id;
        // Promoted videos
        $promotedVideos = Video::with('user:id,channel_name')->where('category_id', $categoryId)
            ->where('visibility', 'Everyone')
            ->where('is_suspend', 0)
            ->where('is_promoted', 1)
            ->latest('id')
            ->take(10)
            ->get()
            ->shuffle()
            ->take(3);
        // Latest non-promoted shuffle
        $nonPromotedVideos = Video::with('user:id,channel_name')->where('category_id', $categoryId)
            ->where('visibility', 'Everyone')
            ->where('is_suspend', 0)
            ->where('is_promoted', 0)
            ->latest('id')
            ->get()

            ->shuffle();

        $related_videos = $promotedVideos->concat($nonPromotedVideos);
        $related_videos = $related_videos->map(function ($video) {
            $video->views_count_formated = Number::abbreviate($video->views);
            $video->created_at_formated  = $video->created_at->diffForHumans();
            return $video;
        });

        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $pagedData   = $related_videos->forPage($currentPage, $perPage)->values();

        $paginated = new LengthAwarePaginator(
            $pagedData,
            $related_videos->count(),
            $perPage,
            $currentPage,
            ['path' => url()->current()]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Related videos retrieved successfully.',
            'data'    => $paginated,
        ], 200);
    }
}
