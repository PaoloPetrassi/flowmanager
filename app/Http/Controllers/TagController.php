<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TagController extends Controller
{
    public function index(): View
    {
        abort_unless(auth()->user()->hasPermission('tags.manage'), 403);

        return view('tags.index', ['tags' => Tag::withCount(['companies', 'projects', 'tasks', 'tickets'])->orderBy('name')->get()]);
    }

    public function store(Request $r): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('tags.manage'), 403);
        $d = $r->validate(['name' => 'required|string|max:80|unique:tags,name', 'color' => 'nullable|string|max:32']);
        Tag::create(['name' => $d['name'], 'slug' => Str::slug($d['name']), 'color' => $d['color'] ?? null]);

        return back()->with('status', __('Tag created.'));
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        abort_unless(auth()->user()->hasPermission('tags.manage'), 403);
        $tag->delete();

        return back()->with('status', __('Tag deleted.'));
    }
}
