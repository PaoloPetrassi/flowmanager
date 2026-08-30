<?php

namespace App\Models\Concerns;

use App\Models\Attachment;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasCollaboration
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
