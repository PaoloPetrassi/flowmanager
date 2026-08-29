<?php

namespace App\Http\Controllers;

use App\Enums\CompanyStatus;
use App\Enums\CompanyType;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Display a listing of the companies.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Company::class);

        $search = trim((string) $request->query('search'));
        $type = (string) $request->query('type');
        $status = (string) $request->query('status');

        $allowedSorts = [
            'name',
            'type',
            'status',
            'city',
            'industry',
            'created_at',
        ];

        $sort = (string) $request->query('sort', 'name');

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'name';
        }

        $direction = strtolower(
            (string) $request->query('direction', 'asc')
        );

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $companies = Company::query()
            ->with('creator')

            ->when(
                $search !== '',
                function (Builder $query) use ($search) {
                    $query->where(function (Builder $query) use ($search) {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('legal_name', 'like', "%{$search}%")
                            ->orWhere('vat_number', 'like', "%{$search}%")
                            ->orWhere('tax_code', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('city', 'like', "%{$search}%");
                    });
                }
            )

            ->when(
                CompanyType::tryFrom($type) !== null,
                fn (Builder $query) => $query->where('type', $type)
            )

            ->when(
                CompanyStatus::tryFrom($status) !== null,
                fn (Builder $query) => $query->where('status', $status)
            )

            ->orderBy($sort, $direction)

            ->paginate(15)
            ->withQueryString();

        return view('companies.index', [
            'companies' => $companies,
            'types' => CompanyType::cases(),
            'statuses' => CompanyStatus::cases(),

            'filters' => [
                'search' => $search,
                'type' => $type,
                'status' => $status,
                'sort' => $sort,
                'direction' => $direction,
            ],
        ]);
    }

    /**
     * Show the form for creating a new company.
     */
    public function create(): View
    {
        Gate::authorize('create', Company::class);

        return view('companies.create', [
            'company' => new Company(),
            'types' => CompanyType::cases(),
            'statuses' => CompanyStatus::cases(),
        ]);
    }

    /**
     * Store a newly created company.
     */
    public function store(
        StoreCompanyRequest $request
    ): RedirectResponse {
        Gate::authorize('create', Company::class);

        $company = Company::create([
            ...$request->validated(),
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('companies.show', $company)
            ->with(
                'status',
                'Company created successfully.'
            );
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company): View
    {
        Gate::authorize('view', $company);

        $company->load('creator');

        return view('companies.show', [
            'company' => $company,
        ]);
    }

    /**
     * Show the form for editing the specified company.
     */
    public function edit(Company $company): View
    {
        Gate::authorize('update', $company);

        return view('companies.edit', [
            'company' => $company,
            'types' => CompanyType::cases(),
            'statuses' => CompanyStatus::cases(),
        ]);
    }

    /**
     * Update the specified company.
     */
    public function update(
        UpdateCompanyRequest $request,
        Company $company
    ): RedirectResponse {
        Gate::authorize('update', $company);

        $company->update(
            $request->validated()
        );

        return redirect()
            ->route('companies.show', $company)
            ->with(
                'status',
                'Company updated successfully.'
            );
    }

    /**
     * Remove the specified company.
     */
    public function destroy(
        Company $company
    ): RedirectResponse {
        Gate::authorize('delete', $company);

        $company->delete();

        return redirect()
            ->route('companies.index')
            ->with(
                'status',
                'Company deleted successfully.'
            );
    }
}