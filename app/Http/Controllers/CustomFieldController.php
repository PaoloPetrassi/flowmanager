<?php
namespace App\Http\Controllers;
use App\Models\CustomField;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;
class CustomFieldController extends Controller {
    public function index():View { abort_unless(auth()->user()->hasPermission('custom-fields.manage'),403); return view('custom-fields.index',['fields'=>CustomField::orderBy('resource_type')->orderBy('sort_order')->get()]); }
    public function store(Request $r):RedirectResponse { abort_unless(auth()->user()->hasPermission('custom-fields.manage'),403); $d=$r->validate(['resource_type'=>'required|in:company,contact,project,task,asset,ticket','name'=>'required|string|max:120','field_type'=>'required|in:text,textarea,number,date,select,checkbox','options'=>'nullable|string','is_required'=>'nullable|boolean']); CustomField::create(['resource_type'=>$d['resource_type'],'name'=>$d['name'],'slug'=>Str::slug($d['name']),'field_type'=>$d['field_type'],'options'=>filled($d['options']??null)?array_values(array_filter(array_map('trim',explode("\n",$d['options'])))):null,'is_required'=>$r->boolean('is_required'),'sort_order'=>(int)CustomField::where('resource_type',$d['resource_type'])->max('sort_order')+1]); return back()->with('status',__('Custom field created.')); }
    public function update(Request $r, CustomField $customField):RedirectResponse { abort_unless(auth()->user()->hasPermission('custom-fields.manage'),403); $d=$r->validate(['name'=>'required|string|max:120','is_active'=>'nullable|boolean','is_required'=>'nullable|boolean']); $customField->update(['name'=>$d['name'],'is_active'=>$r->boolean('is_active'),'is_required'=>$r->boolean('is_required')]); return back()->with('status',__('Custom field updated.')); }
    public function destroy(CustomField $customField):RedirectResponse { abort_unless(auth()->user()->hasPermission('custom-fields.manage'),403); $customField->delete(); return back()->with('status',__('Custom field deleted.')); }
}
