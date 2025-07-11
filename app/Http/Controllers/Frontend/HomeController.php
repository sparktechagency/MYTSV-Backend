<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
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

    public function searchVideo(Request $request)
    {
        $perPage     = $request->per_page ?? 10;
        $currentPage = $request->page ?? 1;

        $baseQuery = Video::with('user:id,channel_name,avatar')->where('visibility', 'Everyone')
            ->where('is_suspend', 0);

        if ($request->category_id) {
            $baseQuery = $baseQuery->where('category_id', $request->category_id);
        }

        if ($request->location) {
            $baseQuery = $baseQuery->where(function ($query) use ($request) {
                $query->where('city', 'LIKE', '%' . $request->location . '%')
                    ->orWhere('states', 'LIKE', '%' . $request->location . '%');
            });
        }

        // Count totals
        $totalNonPromoted = (clone $baseQuery)->where('is_promoted', 0)->count();
        $totalPromoted    = (clone $baseQuery)->where('is_promoted', 1)->count();

        // Get random promoted video on page 1 only
        $promotedVideo = null;
        if ($currentPage == 1 && $totalPromoted > 0) {
            $promotedVideo = (clone $baseQuery)
                ->where('is_promoted', 1)
                ->inRandomOrder()
                ->first();
        }

        // Adjust per page for non-promoted videos
        $nonPromotedPerPage = $promotedVideo ? ($perPage - 1) : $perPage;

        // Calculate offset
        $offset = ($currentPage - 1) * $perPage;
        if ($promotedVideo && $currentPage == 1) {
            $offset = 0; // First page manually added promoted video
        } else if ($promotedVideo) {
            $offset -= 1;
        }

        // Get non-promoted videos
        $nonPromotedVideos = (clone $baseQuery)
            ->where('is_promoted', 0)
            ->latest('id')
            ->offset($offset)
            ->limit($nonPromotedPerPage)
            ->get();

        // Merge videos
        $videos = collect();
        if ($promotedVideo) {
            $videos->push($promotedVideo);
        }
        $videos = $videos->merge($nonPromotedVideos);

        // Total count for paginator
        $total = $totalNonPromoted + ($totalPromoted > 0 ? 1 : 0);

        // Create paginator
        $paginator = new LengthAwarePaginator(
            $videos,
            $total,
            $perPage,
            $currentPage,
            ['path' => url()->current()]
        );

        // Add formatted attributes with map()
        $mappedVideos = $paginator->getCollection()->map(function ($video) {
            $video->views_count_formated = Number::abbreviate($video->views);
            $video->created_at_formated  = $video->created_at->diffForHumans();
            return $video;
        });

        // Replace collection in paginator
        $paginator->setCollection($mappedVideos);

        $data=[
            'category_name'=>Category::where('id',$request->category_id)->first()->name ?? null,
            'videos'=> $paginator,
        ];
        // Return response
        return response()->json([
            'status'  => true,
            'message' => 'Search videos retrieved successfully.',
            'data'    => $data,
        ], 200);
    }

    public function homeVideo(Request $request)
    {
        $videoLimit = $request->video_limit ?? 6;

        $categories = Category::all();

        $categories->map(function ($category) use ($videoLimit) {

            // Get 1 random promoted video with eager loaded user
            $promotedVideo = $category->videos()
                ->with('user:id,channel_name,avatar')
                ->where('is_promoted', 1)
                ->where('visibility', 'Everyone')
                ->where('is_suspend', 0)
                ->inRandomOrder()
                ->first();

            $nonPromotedLimit = $promotedVideo ? ($videoLimit - 1) : $videoLimit;

            // Get non-promoted videos with eager loaded user
            $nonPromotedVideos = $category->videos()
                ->with('user:id,channel_name,avatar')
                ->where('is_promoted', 0)
                ->where('visibility', 'Everyone')
                ->where('is_suspend', 0)
                ->latest('id')
                ->limit($nonPromotedLimit)
                ->get()
                ->shuffle();

            // Merge videos
            $videos = collect();
            if ($promotedVideo) {
                $videos->push($promotedVideo);
            }
            $videos = $videos->merge($nonPromotedVideos);

            $videos = $videos->map(function ($video) {
                $video->views_count_formated = Number::abbreviate($video->views);
                $video->created_at_formated  = $video->created_at->diffForHumans();
                return $video;
            });

            // Attach videos back to category relation
            $category->setRelation('videos', $videos);

            return $category;
        });

        return response()->json([
            'status'  => true,
            'message' => 'Home page videos retrieved successfully.',
            'data'    => $categories,
        ], 200);
    }
}
