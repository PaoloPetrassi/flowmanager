<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
class Attachment extends Model {
 protected $fillable=['user_id','attachable_type','attachable_id','original_name','disk','path','mime_type','extension','size','document_category','document_status','version','parent_attachment_id','expires_at','approved_by','approved_at','checksum'];
 protected function casts():array{return ['size'=>'integer','expires_at'=>'date','approved_at'=>'datetime','version'=>'integer'];}
 public function user():BelongsTo{return $this->belongsTo(User::class);}
 public function approvedBy():BelongsTo{return $this->belongsTo(User::class,'approved_by');}
 public function parent():BelongsTo{return $this->belongsTo(self::class,'parent_attachment_id');}
 public function attachable():MorphTo{return $this->morphTo();}
 public function formattedSize():string{if($this->size<1024)return $this->size.' B';if($this->size<1048576)return number_format($this->size/1024,1).' KB';return number_format($this->size/1048576,1).' MB';}
}
