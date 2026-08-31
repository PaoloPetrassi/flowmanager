<?php
namespace App\Http\Controllers;
use App\Models\ImportRun;
use App\Services\CsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
class ImportController extends Controller {
 public function __construct(private CsvImportService $service){}
 public function index():View{abort_unless(auth()->user()->hasPermission('imports.manage'),403);return view('imports.index',['resources'=>$this->service->resources(),'runs'=>ImportRun::where('user_id',auth()->id())->latest()->limit(15)->get()]);}
 public function preview(Request $r):View{abort_unless(auth()->user()->hasPermission('imports.manage'),403);$d=$r->validate(['resource_type'=>'required|in:companies,contacts,projects,tasks,tickets','file'=>'required|file|mimes:csv,txt,xlsx|max:10240']);$path=$r->file('file')->store('imports/tmp');$preview=$this->service->preview(Storage::path($path));return view('imports.preview',['resource'=>$d['resource_type'],'storedPath'=>$path,'preview'=>$preview,'fields'=>$this->service->resources()[$d['resource_type']]['fields']]);}
 public function store(Request $r):RedirectResponse{abort_unless(auth()->user()->hasPermission('imports.manage'),403);$d=$r->validate(['resource_type'=>'required','stored_path'=>'required','mapping'=>'required|array']);$result=$this->service->import($d['resource_type'],Storage::path($d['stored_path']),$d['mapping']);ImportRun::create(['user_id'=>auth()->id(),'resource_type'=>$d['resource_type'],'original_filename'=>basename($d['stored_path']),'total_rows'=>$result['total'],'imported_rows'=>$result['imported'],'failed_rows'=>$result['failed'],'errors'=>$result['errors']]);Storage::delete($d['stored_path']);return redirect()->route('imports.index')->with('status',__('Import completed: :ok imported, :failed failed.',['ok'=>$result['imported'],'failed'=>$result['failed']]));}
}
