<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\Company; use App\Models\Contact; use App\Models\Project; use App\Models\Task; use App\Models\Ticket; use App\Models\Asset;
use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;
class ResourceController extends Controller {
 private function config(string $resource):array { return ['companies'=>[Company::class,'companies.view'],'contacts'=>[Contact::class,'contacts.view'],'projects'=>[Project::class,'projects.view'],'tasks'=>[Task::class,'tasks.view'],'tickets'=>[Ticket::class,'tickets.view'],'assets'=>[Asset::class,'assets.view']][$resource]??abort(404); }
 public function index(Request $r,string $resource):JsonResponse{$this->ensureAbility($r,'read');$cfg=$this->config($resource);abort_unless($r->user()->hasPermission($cfg[1]),403);$q=$cfg[0]::query();if(method_exists($cfg[0],'scopeOperational'))$q->operational();$rows=$q->latest('id')->paginate(min(max((int)$r->query('per_page',25),1),100));return response()->json($rows);}
 public function show(Request $r,string $resource,int $id):JsonResponse{$this->ensureAbility($r,'read');$cfg=$this->config($resource);abort_unless($r->user()->hasPermission($cfg[1]),403);$row=$cfg[0]::findOrFail($id);return response()->json(['data'=>$row]);}
 private function ensureAbility(Request $r,string $ability):void{abort_unless(in_array($ability,$r->attributes->get('apiToken')?->abilities??[],true),403);}
}
