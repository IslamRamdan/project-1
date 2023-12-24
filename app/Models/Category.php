<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'image',
        'section_id'
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
