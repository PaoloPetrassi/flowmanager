<?php

namespace App\Http\Controllers;

use App\Enums\AssetStatus;
use App\Http\Requests\AssignAssetRequest;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Asset::class);

        $search = trim((string) $request->query('search'));
        $companyId = (string) $request->query('company_id');
        $status = (string) $request->query('status');
        $assigneeId = (string) $request->query('assigned_to');
        $category = trim((string) $request->query('category'));

        $allowedSorts = [
            'asset_tag',
            'name',
            'category',
            'status',
            'purchase_date',
            'created_at',
        ];

        $sort = (string) $request->query('sort', 'asset_tag');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'asset_tag';
        }

        $direction = strtolower((string) $request->query('direction', 'asc'));

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $assets = Asset::query()
            ->with([
                'company:id,name',
                'assignee:id,name',
            ])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('asset_tag', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhereHas(
                            'company',
                            fn (Builder $companyQuery) => $companyQuery
                                ->where('name', 'like', "%{$search}%")
                        );
                });
            })
            ->when(
                ctype_digit($companyId),
                fn (Builder $query) => $query->where('company_id', (int) $companyId)
            )
            ->when(
                AssetStatus::tryFrom($status) !== null,
                fn (Builder $query) => $query->where('status', $status)
            )
            ->when(
                ctype_digit($assigneeId),
                fn (Builder $query) => $query->where('assigned_to', (int) $assigneeId)
            )
            ->when(
                $category !== '',
                fn (Builder $query) => $query->where('category', $category)
            )
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();

        $categories = Asset::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('assets.index', [
            'assets' => $assets,
            'companies' => $this->companyOptions(),
            'users' => $this->userOptions(),
            'statuses' => AssetStatus::cases(),
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'company_id' => $companyId,
                'status' => $status,
                'assigned_to' => $assigneeId,
                'category' => $category,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Asset::class);

        return view('assets.create', $this->formData(new Asset([
            'status' => AssetStatus::Available,
        ])));
    }

    public function store(StoreAssetRequest $request): RedirectResponse
    {
        Gate::authorize('create', Asset::class);

        $asset = Asset::create([
            ...$this->normalizeAssignment($request->validated()),
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('assets.show', $asset)
            ->with('status', 'Asset created successfully.');
    }

    public function show(Asset $asset): View
    {
        Gate::authorize('view', $asset);

        $asset->load([
            'company',
            'assignee',
            'creator',
        ]);

        return view('assets.show', [
            'asset' => $asset,
        ]);
    }

    public function edit(Asset $asset): View
    {
        Gate::authorize('update', $asset);

        return view('assets.edit', $this->formData($asset));
    }

    public function update(
        UpdateAssetRequest $request,
        Asset $asset
    ): RedirectResponse {
        Gate::authorize('update', $asset);

        $asset->update(
            $this->normalizeAssignment($request->validated())
        );

        return redirect()
            ->route('assets.show', $asset)
            ->with('status', 'Asset updated successfully.');
    }

    public function editAssignment(Asset $asset): View
    {
        Gate::authorize('assign', $asset);

        return view('assets.assignment', [
            'asset' => $asset,
            'users' => $this->userOptions(),
        ]);
    }

    public function updateAssignment(
        AssignAssetRequest $request,
        Asset $asset
    ): RedirectResponse {
        Gate::authorize('assign', $asset);

        $assignedTo = $request->validated('assigned_to');

        $asset->update([
            'assigned_to' => $assignedTo,
            'status' => $assignedTo
                ? AssetStatus::Assigned
                : AssetStatus::Available,
        ]);

        return redirect()
            ->route('assets.show', $asset)
            ->with('status', 'Asset assignment updated successfully.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        Gate::authorize('delete', $asset);

        $asset->delete();

        return redirect()
            ->route('assets.index')
            ->with('status', 'Asset deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Asset $asset): array
    {
        return [
            'asset' => $asset,
            'companies' => $this->companyOptions(),
            'users' => $this->userOptions(),
            'statuses' => AssetStatus::cases(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeAssignment(array $data): array
    {
        if (! empty($data['assigned_to']) && $data['status'] === AssetStatus::Available->value) {
            $data['status'] = AssetStatus::Assigned->value;
        }

        if (empty($data['assigned_to']) && $data['status'] === AssetStatus::Assigned->value) {
            $data['status'] = AssetStatus::Available->value;
        }

        return $data;
    }

    private function companyOptions()
    {
        return Company::query()
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function userOptions()
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
