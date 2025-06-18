<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $guarded = ['id'];
    public function getImageAttribute($iamge)
    {
        return asset('uploads/banner/' . $iamge);
    }
}
