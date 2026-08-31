<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SavedReport extends Model { protected $fillable=['user_id','name','dataset','filters','metrics','chart_type','is_shared']; protected function casts():array{return ['filters'=>'array','metrics'=>'array','is_shared'=>'boolean'];} }
