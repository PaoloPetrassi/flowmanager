<?php
namespace App\Services;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
class WebhookService {
 public static function dispatch(string $event, Model $model, array $data=[]):void {
  if(app()->environment('testing')) return;
  Webhook::query()->where('is_active',true)->get()->filter(fn($w)=>in_array($event,$w->events??[],true))->each(function($w) use($event,$model,$data){
   $payload=['event'=>$event,'occurred_at'=>now()->toIso8601String(),'resource'=>class_basename($model),'id'=>$model->getKey(),'data'=>$data];
   try{$json=json_encode($payload,JSON_UNESCAPED_SLASHES);$res=Http::timeout(5)->withHeaders(['X-FlowManager-Event'=>$event,'X-FlowManager-Signature'=>hash_hmac('sha256',$json,$w->secret??'')])->post($w->url,$payload);WebhookDelivery::create(['webhook_id'=>$w->id,'event'=>$event,'payload'=>$payload,'status_code'=>$res->status(),'successful'=>$res->successful(),'response_body'=>mb_substr($res->body(),0,4000),'delivered_at'=>now()]);}catch(\Throwable $e){WebhookDelivery::create(['webhook_id'=>$w->id,'event'=>$event,'payload'=>$payload,'successful'=>false,'response_body'=>mb_substr($e->getMessage(),0,4000),'delivered_at'=>now()]);}
  });
 }
}
