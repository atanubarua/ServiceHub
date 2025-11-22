<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    protected $guarded = [];

    public function vendor() {
        return $this->belongsTo(Vendor::class);
    }

    public function category() {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function service_images() {
        return $this->hasMany(ServiceImage::class);
    }
}
