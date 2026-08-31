<?php
namespace App\Http\Controllers;
use App\Models\SavedFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class SavedFilterController extends Controller {
 public function store(Request $r):RedirectResponse{$d=$r->validate(['resource_type'=>'required|in:companies,contacts,projects,tasks,assets,tickets','name'=>'required|string|max:100','filters'=>'required|array']);auth()->user()->savedFilters()->create($d);return back()->with('status',__('Filter saved.'));}
 public function destroy(SavedFilter $savedFilter):RedirectResponse{abort_unless($savedFilter->user_id===auth()->id(),403);$savedFilter->delete();return back()->with('status',__('Saved filter deleted.'));}
}
