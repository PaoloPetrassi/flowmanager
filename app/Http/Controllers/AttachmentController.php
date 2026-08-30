<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Attachment;
use App\Services\AuditService;
use App\Support\FlowResourceRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function store(
        StoreAttachmentRequest $request,
        string $type,
        int $id
    ): RedirectResponse {
        $target = FlowResourceRegistry::find($type, $id);

        Gate::authorize('view', $target);

        $file = $request->file('file');
        $path = $file->store(
            'flowmanager/attachments/'.now()->format('Y/m'),
            'local'
        );

        $attachment = $target->attachments()->create([
            'user_id' => $request->user()->id,
            'original_name' => $file->getClientOriginalName(),
            'disk' => 'local',
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'extension' => strtolower($file->getClientOriginalExtension()),
            'size' => $file->getSize(),
        ]);

        AuditService::record(
            $target,
            'attachment_added',
            [],
            [
                'attachment_id' => $attachment->id,
                'file' => $attachment->original_name,
            ]
        );

        return back()->with(
            'status',
            __('Attachment uploaded successfully.')
        );
    }

    public function download(Attachment $attachment): StreamedResponse
    {
        $target = $attachment->attachable;

        abort_unless($target, 404);
        Gate::authorize('view', $target);
        abort_unless(
            Storage::disk($attachment->disk)->exists($attachment->path),
            404
        );

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->original_name
        );
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        $user = auth()->user();
        $target = $attachment->attachable;

        abort_unless($target, 404);
        Gate::authorize('view', $target);

        abort_unless(
            $attachment->user_id === $user->id
                || $user->hasPermission('attachments.delete'),
            403
        );

        Storage::disk($attachment->disk)->delete($attachment->path);

        $attachmentId = $attachment->id;
        $fileName = $attachment->original_name;
        $attachment->delete();

        AuditService::record(
            $target,
            'attachment_deleted',
            [
                'attachment_id' => $attachmentId,
                'file' => $fileName,
            ],
            []
        );

        return back()->with(
            'status',
            __('Attachment deleted successfully.')
        );
    }
}
