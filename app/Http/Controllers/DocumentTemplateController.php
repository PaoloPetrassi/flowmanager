<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Services\DocumentGenerationService;
use App\Support\FlowResourceRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DocumentTemplateController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('documents.manage'), 403);

        return view('documents.templates', [
            'templates' => DocumentTemplate::latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('documents.manage'), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'resource_type' => ['required', 'in:company,contact,project,task,asset,ticket'],
            'default_format' => ['required', 'in:pdf,doc'],
            'content' => ['required', 'string', 'max:30000'],
        ]);

        DocumentTemplate::create($data + ['created_by' => auth()->id(), 'is_active' => true]);

        return back()->with('status', __('Document template created.'));
    }

    public function destroy(DocumentTemplate $documentTemplate): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('documents.manage'), 403);
        $documentTemplate->delete();

        return back()->with('status', __('Document template deleted.'));
    }

    public function generate(Request $request, DocumentTemplate $documentTemplate, string $type, int $id, DocumentGenerationService $generator): Response
    {
        abort_unless(auth()->user()->hasPermission('documents.view'), 403);
        abort_unless($documentTemplate->is_active && $documentTemplate->resource_type === $type, 404);

        $target = FlowResourceRegistry::find($type, $id);
        $this->authorize('view', $target);
        $target->loadMissing($this->safeRelations($type));

        $format = $request->query('format', $documentTemplate->default_format);
        abort_unless(in_array($format, ['pdf', 'doc'], true), 404);
        $body = $generator->renderTemplate($documentTemplate->content, $target);
        $filename = Str::slug($documentTemplate->name.'-'.FlowResourceRegistry::labelForModel($target)).'.'.$format;

        if ($format === 'doc') {
            return response($generator->word($documentTemplate->name, $body), 200, [
                'Content-Type' => 'application/msword; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            ]);
        }

        return response($generator->pdf($documentTemplate->name, $body), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function safeRelations(string $type): array
    {
        return match ($type) {
            'project' => ['company', 'contact', 'manager'],
            'task' => ['project', 'assignee'],
            'ticket' => ['company', 'contact', 'assignee'],
            'contact' => ['company'],
            'asset' => ['company', 'assignee'],
            default => [],
        };
    }
}
