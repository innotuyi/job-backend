<?php

namespace App\Models;

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
        'posted_date',
        'deadline',
        'location',
        'description',
        'categoryID'

    ];
}
