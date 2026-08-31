<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class WebhookDelivery extends Model { protected $fillable=['webhook_id','event','payload','status_code','successful','response_body','delivered_at']; protected function casts():array{return ['payload'=>'array','successful'=>'boolean','delivered_at'=>'datetime'];} }
