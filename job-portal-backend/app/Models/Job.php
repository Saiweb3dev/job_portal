<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Job extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'location',
        'salary',
        'type',
        'user_id',
        'category_id',
        'deadline',
        'status',
        'applications_count',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
