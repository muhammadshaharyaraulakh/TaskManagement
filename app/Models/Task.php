<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    /** Fields a user is allowed to fill in. */
    protected $fillable = [
        'name',
        'project_id',
        'priority',
        'user_id',
    ];

    /** Make sure priority is always a number in JSON and in code. */
    protected $casts = [
        'priority' => 'integer',
    ];

    /** The user who owns this task. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The project this task belongs to. */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Only tasks for one project, ordered by priority.
     * Used by the dashboard and the reorder endpoint.
     */
    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId)->orderBy('priority');
    }
}
