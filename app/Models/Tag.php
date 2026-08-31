<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
class Tag extends Model { protected $fillable=['name','slug','color']; public function companies(): MorphToMany { return $this->morphedByMany(Company::class,'taggable'); } public function projects(): MorphToMany { return $this->morphedByMany(Project::class,'taggable'); } public function tasks(): MorphToMany { return $this->morphedByMany(Task::class,'taggable'); } public function tickets(): MorphToMany { return $this->morphedByMany(Ticket::class,'taggable'); } }
