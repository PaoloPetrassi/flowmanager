<?php
namespace App\Http\Controllers;
use App\Models\Attachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
class DocumentController extends Controller {
 public function index(Request $r):View{abort_unless(auth()->user()->hasPermission('documents.view'),403);$q=Attachment::with(['user','approvedBy'])->latest();if($r->filled('category'))$q->where('document_category',(string) $r->string('category'));if($r->filled('status'))$q->where('document_status',(string) $r->string('status'));if($r->filled('expiring'))$q->whereBetween('expires_at',[today(),today()->addDays(30)]);return view('documents.index',['documents'=>$q->paginate(25)->withQueryString()]);}
 public function approve(Attachment $attachment):RedirectResponse{abort_unless(auth()->user()->hasPermission('documents.approve'),403);$attachment->update(['document_status'=>'approved','approved_by'=>auth()->id(),'approved_at'=>now()]);return back()->with('status',__('Document approved.'));}
 public function reject(Attachment $attachment):RedirectResponse{abort_unless(auth()->user()->hasPermission('documents.approve'),403);$attachment->update(['document_status'=>'rejected','approved_by'=>auth()->id(),'approved_at'=>now()]);return back()->with('status',__('Document rejected.'));}
 public function newVersion(Request $r,Attachment $attachment):RedirectResponse{abort_unless(auth()->user()->hasPermission('attachments.create'),403);$d=$r->validate(['file'=>'required|file|max:10240']);$file=$r->file('file');$path=$file->store('flowmanager/attachments/'.now()->format('Y/m'),'local');$new=$attachment->replicate(['id','created_at','updated_at']);$new->path=$path;$new->original_name=$file->getClientOriginalName();$new->mime_type=$file->getMimeType();$new->size=$file->getSize();$new->version=$attachment->version+1;$new->parent_attachment_id=$attachment->parent_attachment_id?:$attachment->id;$new->document_status='active';$new->approved_by=null;$new->approved_at=null;$new->checksum=hash_file('sha256',Storage::disk('local')->path($path));$new->user_id=auth()->id();$new->disk='local';$new->extension=strtolower($file->getClientOriginalExtension());$new->save();$attachment->update(['document_status'=>'superseded']);return back()->with('status',__('New document version uploaded.'));}
}
