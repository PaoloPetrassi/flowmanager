<div class="row g-4 mt-1">
    <div class="col-12 col-xl-7">
        <div class="card fm-card h-100">
            <div class="card-header fm-card-header">
                <div>
                    <h2 class="fm-card-title">{{ __('Discussion') }}</h2>
                    <p class="fm-card-subtitle">{{ __('Comments and operational notes shared on this record') }}</p>
                </div>
                <span class="badge text-bg-light border">{{ $collaboration['comments']->count() }}</span>
            </div>

            @if (auth()->user()->hasPermission('comments.create'))
                <div class="card-body border-bottom">
                    <form method="POST" action="{{ route('comments.store', [$collaborationType, $collaborationTarget->id]) }}">
                        @csrf

                        <label for="collaboration-comment" class="form-label fw-semibold">{{ __('Add comment') }}</label>
                        <textarea
                            id="collaboration-comment"
                            name="body"
                            rows="3"
                            maxlength="5000"
                            class="form-control @error('body') is-invalid @enderror"
                            placeholder="{{ __('Write an update, note or decision...') }}"
                            required
                        >{{ old('body') }}</textarea>

                        @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-send me-1"></i>
                                {{ __('Publish comment') }}
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            <div class="fm-collaboration-list">
                @forelse ($collaboration['comments'] as $comment)
                    <article class="fm-comment-item">
                        <div class="fm-comment-avatar">
                            {{ strtoupper(substr($comment->user?->name ?: '?', 0, 1)) }}
                        </div>

                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold">{{ $comment->user?->name ?: __('Deleted user') }}</div>
                                    <div class="small text-secondary">
                                        {{ $comment->created_at->format('d/m/Y H:i') }}
                                        · {{ $comment->created_at->diffForHumans() }}
                                    </div>
                                </div>

                                @if ($comment->user_id === auth()->id() || auth()->user()->hasPermission('comments.delete'))
                                    <form method="POST" action="{{ route('comments.destroy', $comment) }}" onsubmit="return confirm(@js(__('Delete this comment?')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-1" title="{{ __('Delete') }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="fm-comment-body mt-2">{!! nl2br(e($comment->body)) !!}</div>
                        </div>
                    </article>
                @empty
                    <div class="p-4 text-center text-secondary">
                        <i class="bi bi-chat-left-text d-block fs-4 mb-2"></i>
                        {{ __('No comments yet.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="card fm-card mb-4">
            <div class="card-header fm-card-header">
                <div>
                    <h2 class="fm-card-title">{{ __('Attachments') }}</h2>
                    <p class="fm-card-subtitle">{{ __('Private files linked to this record') }}</p>
                </div>
                <span class="badge text-bg-light border">{{ $collaboration['attachments']->count() }}</span>
            </div>

            @if (auth()->user()->hasPermission('attachments.create'))
                <div class="card-body border-bottom">
                    <form method="POST" action="{{ route('attachments.store', [$collaborationType, $collaborationTarget->id]) }}" enctype="multipart/form-data">
                        @csrf

                        <label for="collaboration-file" class="form-label fw-semibold">{{ __('Upload file') }}</label>
                        <input id="collaboration-file" type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.png,.jpg,.jpeg,.zip" required>
                        <div class="row g-2 mt-1">
                            <div class="col-sm-7"><input name="document_category" class="form-control form-control-sm" maxlength="60" placeholder="{{ __('Document category') }}"></div>
                            <div class="col-sm-5"><input name="expires_at" type="date" class="form-control form-control-sm" title="{{ __('Expiration date') }}"></div>
                        </div>
                        <div class="d-flex justify-content-end mt-2"><button type="submit" class="btn btn-outline-primary btn-sm"><i class="bi bi-upload"></i><span class="ms-1">{{ __('Upload') }}</span></button></div>

                        @error('file')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror

                        <div class="form-text">{{ __('PDF, Office, CSV, text, images or ZIP. Maximum 10 MB.') }}</div>
                    </form>
                </div>
            @endif

            <div class="list-group list-group-flush">
                @forelse ($collaboration['attachments'] as $attachment)
                    <div class="list-group-item fm-attachment-item">
                        <div class="fm-file-icon">
                            <i class="bi bi-paperclip"></i>
                        </div>

                        <div class="flex-grow-1 min-w-0">
                            <a href="{{ route('attachments.download', $attachment) }}" class="fw-semibold text-dark d-block text-truncate">
                                {{ $attachment->original_name }}
                            </a>
                            <div class="small text-secondary">
                                {{ $attachment->formattedSize() }} · v{{ $attachment->version }} @if($attachment->document_category) · {{ $attachment->document_category }} @endif
                                · {{ $attachment->user?->name ?: __('Deleted user') }}
                                · {{ $attachment->created_at->format('d/m/Y H:i') }}
                            </div>
                        </div>

                        @if ($attachment->user_id === auth()->id() || auth()->user()->hasPermission('attachments.delete'))
                            <form method="POST" action="{{ route('attachments.destroy', $attachment) }}" onsubmit="return confirm(@js(__('Delete this attachment?')));">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="p-4 text-center text-secondary">
                        {{ __('No attachments yet.') }}
                    </div>
                @endforelse
            </div>
        </div>

        @if (auth()->user()->hasPermission('audit.view'))
            <div class="card fm-card">
                <div class="card-header fm-card-header">
                    <div>
                        <h2 class="fm-card-title">{{ __('Recent activity') }}</h2>
                        <p class="fm-card-subtitle">{{ __('Latest tracked changes on this record') }}</p>
                    </div>
                    <a href="{{ route('activity.index', ['search' => $collaborationTarget instanceof App\Models\Contact ? $collaborationTarget->full_name : App\Support\FlowResourceRegistry::labelForModel($collaborationTarget)]) }}" class="btn btn-sm btn-outline-secondary">
                        {{ __('View all') }}
                    </a>
                </div>

                <div class="list-group list-group-flush">
                    @forelse ($collaboration['auditLogs'] as $log)
                        <div class="list-group-item fm-activity-mini">
                            <span class="fm-activity-dot"></span>
                            <div class="min-w-0">
                                <div class="small">
                                    <strong>{{ $log->user?->name ?: __('System') }}</strong>
                                    {{ __(str_replace('_', ' ', $log->event)) }}
                                </div>
                                <div class="small text-secondary">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-secondary">{{ __('No tracked activity yet.') }}</div>
                    @endforelse
                </div>
            </div>
        @endif
    </div>
</div>
