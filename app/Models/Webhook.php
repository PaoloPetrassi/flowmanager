<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Webhook extends Model { protected $fillable=['name','url','secret','events','is_active','created_by']; protected function casts():array{return ['events'=>'array','is_active'=>'boolean'];} public function deliveries():HasMany{return $this->hasMany(WebhookDelivery::class);} }
