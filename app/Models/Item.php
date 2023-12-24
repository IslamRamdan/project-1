<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'details',
        'images',
        'price',
        'coin',
        'available',
        'rate',
        'category_id'
    ];
    protected $casts = [
        'images' =>  'array',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
