<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Services\AuditService;
use App\Services\CollaborationService;
use App\Support\FlowResourceRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class CommentController extends Controller
{
    public function store(
        StoreCommentRequest $request,
        string $type,
        int $id
    ): RedirectResponse {
        $target = FlowResourceRegistry::find($type, $id);

        Gate::authorize('view', $target);

        $comment = $target->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);

        AuditService::record(
            $target,
            'commented',
            [],
            [
                'comment_id' => $comment->id,
                'comment' => Str::limit($comment->body, 250),
            ]
        );

        CollaborationService::notifyCommentAdded(
            $target,
            $request->user()
        );

        return back()->with(
            'status',
            __('Comment added successfully.')
        );
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $user = auth()->user();
        $target = $comment->commentable;

        abort_unless($target, 404);
        Gate::authorize('view', $target);

        abort_unless(
            $comment->user_id === $user->id
                || $user->hasPermission('comments.delete'),
            403
        );

        $commentId = $comment->id;
        $comment->delete();

        AuditService::record(
            $target,
            'comment_deleted',
            ['comment_id' => $commentId],
            []
        );

        return back()->with(
            'status',
            __('Comment deleted successfully.')
        );
    }
}
