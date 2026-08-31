<?php
namespace App\Http\Controllers;
use App\Models\Webhook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class WebhookController extends Controller {
 public function index():View{abort_unless(auth()->user()->hasPermission('integrations.manage'),403);return view('integrations.webhooks',['webhooks'=>Webhook::withCount('deliveries')->latest()->get()]);}
 public function store(Request $r):RedirectResponse{abort_unless(auth()->user()->hasPermission('integrations.manage'),403);$d=$r->validate(['name'=>'required|string|max:100','url'=>'required|url|max:500','events'=>'required|array|min:1','events.*'=>'in:created,updated,deleted,task.completed,ticket.resolved']);$secret=bin2hex(random_bytes(16));Webhook::create($d+['secret'=>$secret,'created_by'=>auth()->id(),'is_active'=>true]);return back()->with('status',__('Webhook created. Copy the signing secret now: :secret',['secret'=>$secret]));}
 public function destroy(Webhook $webhook):RedirectResponse{abort_unless(auth()->user()->hasPermission('integrations.manage'),403);$webhook->delete();return back()->with('status',__('Webhook deleted.'));}
}
