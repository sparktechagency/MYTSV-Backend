<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\LikedVideo;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
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
        //  views_analytics
        if ($request->type === 'web') {
            $now         = Carbon::now();
            $daysInMonth = $now->daysInMonth;

            $counts = DB::table('watch_histories')
                ->whereIn('video_id', $video_ids)
                ->select(DB::raw('DAY(created_at) as day'), DB::raw('COUNT(*) as total_watch'))
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)
                ->groupBy(DB::raw('DAY(created_at)'))
                ->pluck('total_watch', 'day');

            $views_analytics = collect(range(1, $daysInMonth))->map(function ($day) use ($counts) {
                return [
                    'day'         => $day,
                    'total_watch' => $counts->get($day, 0),
                ];
            });
        }
        if ($request->type === 'app') {
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek   = Carbon::now()->endOfWeek();

            $weekly_counts = DB::table('watch_histories')
                ->whereIn('video_id', $video_ids)
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total_watch'))
                ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('total_watch', 'date');

            $views_analytics = collect(Carbon::parse($startOfWeek)->daysUntil($endOfWeek))->map(function ($date) use ($weekly_counts) {
                return [
                    'day'         => $date->format('D'),
                    'total_watch' => $weekly_counts->get($date->toDateString(), 0),
                ];
            });

        }
        $data = [
            'user'            => [
                'channel_name' => Auth::user()->channel_name,
                'cover_image'  => Auth::user()->cover_image,
                'avatar'       => Auth::user()->avatar,
                'bio'          => Auth::user()->bio,
                'services'     => Auth::user()->services,
                'locations'    => Auth::user()->locations,
            ],
            'views'           => Video::where('user_id', Auth::id())->sum('views'),
            'videos'          => Video::where('user_id', Auth::id())->count(),
            'likes'           => LikedVideo::whereIn('video_id', $video_ids)->count(),
            'views_analytics' => $views_analytics,
        ];
        return response()->json([
            'status'  => true,
            'message' => 'Dashboard data retreived successfully.',
            'data'    => $data,
        ], 200);
    }
}
