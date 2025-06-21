<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $guarded = ['id'];
    public function getVideoAttribute($video)
    {
        return asset('uploads/video/' . $video);
    }
    public function getThumbnailAttribute($thumbnail)
    {
        return asset('uploads/thumbnail/' . $thumbnail);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTagsAttribute($value)
    {
        return json_decode($value);
    }
}
