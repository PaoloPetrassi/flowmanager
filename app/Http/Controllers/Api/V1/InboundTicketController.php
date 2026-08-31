<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\TicketCategory;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InboundTicketController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        abort_unless(in_array('write', $request->attributes->get('apiToken')?->abilities ?? [], true), 403);
        abort_unless($request->user()->hasPermission('tickets.create'), 403);

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:20000'],
            'from_email' => ['nullable', 'email', 'max:255'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'contact_id' => ['nullable', 'integer', 'exists:contacts,id'],
            'priority' => ['nullable', Rule::enum(TicketPriority::class)],
            'category' => ['nullable', Rule::enum(TicketCategory::class)],
        ]);

        $contact = null;
        if (! empty($data['contact_id'])) {
            $contact = Contact::find($data['contact_id']);
        } elseif (! empty($data['from_email'])) {
            $contact = Contact::where('email', $data['from_email'])->first();
        }

        $companyId = $data['company_id'] ?? $contact?->company_id;

        $ticket = Ticket::create([
            'company_id' => $companyId,
            'contact_id' => $contact?->id,
            'reference' => 'TKT-'.now()->format('Y').'-'.strtoupper(Str::random(6)),
            'subject' => $data['subject'],
            'description' => $data['body'].(! empty($data['from_email']) ? "\n\nInbound sender: {$data['from_email']}" : ''),
            'category' => $data['category'] ?? TicketCategory::Other->value,
            'status' => TicketStatus::Open,
            'priority' => $data['priority'] ?? TicketPriority::Medium->value,
            'created_by' => $request->user()->id,
        ]);

        return response()->json(['data' => $ticket, 'url' => route('tickets.show', $ticket)], 201);
    }
}
