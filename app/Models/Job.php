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
}
