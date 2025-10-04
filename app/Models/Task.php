<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'is_done',
        'due_date',
        'priority',
        'creator_id',
        'assigned_to_id'
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'due_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function keywords()
    {
        return $this->belongsToMany(Keywords::class, 'keyword_task', 'task_id', 'keyword_id')->withTimestamps();
    }

    public function assignees()
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps()->withPivot('assigned_at');
    }
}
