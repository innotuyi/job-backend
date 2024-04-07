<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;
    protected $table = 'Job';

    protected $fillable = [
        'title',
        'slug',
        'status',
        'location',
        'photo1',
        'job',
        'posted_date',
        'deadline',
        'location',
        'description',
        'document',
        'categoryID',
        'views_count'

    ];


    public function scopeActive(Builder $query)
    {
        return $query->whereDate('deadline', '>=', now()->toDateString());
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['title'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }
}
