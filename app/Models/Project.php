<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    /** Fields a user is allowed to fill in. */
    protected $fillable = [
        'name',
        'user_id',
    ];

    /** The user who owns this project. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The tasks in this project, top priority first. */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('priority');
    }
}
