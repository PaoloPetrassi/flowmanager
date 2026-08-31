<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class UserPreference extends Model { protected $fillable=['user_id','theme','density','dashboard_widgets','table_preferences']; protected function casts():array{return ['dashboard_widgets'=>'array','table_preferences'=>'array'];} }
