<?php
namespace App\Http\Controllers\api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AnalyticsController extends Controller
{
    public function analytics(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:web,app',
        ]);
        if ($validator->fails()) {
            $firstError = collect($validator->errors()->all())->first();
            return response()->json([
                'message' => $firstError,
                'errors'  => $validator->errors(),
            ], 422);
        }
        $video_ids = Video::where('user_id', Auth::id())->pluck('id');

        if ($request->type === 'web') {
            $now       = Carbon::now();
            $startDate = $now->copy()->subDays(29);

            // Views
            $views = Video::where('user_id', Auth::id())
                ->withCount(['watch_histories as views_count' => function ($query) use ($startDate, $now) {
                    $query->whereBetween('created_at', [$startDate, $now]);
                }])
                ->get()
                ->sum('views_count');

            // Likes
            $likes = Video::where('user_id', Auth::id())
                ->withCount(['likes as likes_count' => function ($query) use ($startDate, $now) {
                    $query->whereBetween('created_at', [$startDate, $now]);
                }])
                ->get()
                ->sum('likes_count');

            // Dislikes
            $dislikes = Video::where('user_id', Auth::id())
                ->withCount(['dislikes as dislikes_count' => function ($query) use ($startDate, $now) {
                    $query->whereBetween('created_at', [$startDate, $now]);
                }])
                ->get()
                ->sum('dislikes_count');


            $counts = DB::table('watch_histories')
                ->whereIn('video_id', $video_ids)
                ->whereBetween('created_at', [$startDate, $now])
                ->select(DB::raw("DATE(created_at) as date"), DB::raw("COUNT(*) as total_watch"))
                ->groupBy(DB::raw("DATE(created_at)"))
                ->pluck('total_watch', 'date');

            $views_analytics = collect();
            foreach (range(0, 29) as $i) {
                $date = $startDate->copy()->addDays($i)->toDateString();
                $views_analytics->push([
                    'date'        => $date,
                    'total_watch' => $counts->get($date, 0),
                ]);
            }
        }

        if ($request->type === 'app') {

        }
        $data = [
            'views'     => $views,
            'likes'     => $likes,
            'dislikes'  => $dislikes,
            'analytics' => $views_analytics,
        ];
        return response()->json([
            'status'  => true,
            'message' => 'Analytics data retreived successfully.',
            'data'    => $data,
        ], 200);
    }
}
