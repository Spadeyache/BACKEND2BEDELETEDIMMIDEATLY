<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\DesignCatalogProduct;
use App\Models\DesignLabelGroup;
use App\Services\DesignCatalog\DesignCatalogAutoImportService;
use Illuminate\Http\Request;

class DesignCatalogController extends Controller
{
    public function index(Request $request)
    {
        app(DesignCatalogAutoImportService::class)->maybeRun();

        $status = $request->get('status', 'draft');

        $query = DesignCatalogProduct::query()
            ->with(['labels.group'])
            ->when($status !== 'all', fn ($builder) => $builder->where('status', $status))
            ->when($request->filled('design_type'), fn ($builder) => $builder->where('design_type', $request->design_type))
            ->when($request->filled('mood'), fn ($builder) => $builder->where('mood', $request->mood))
            ->when($request->filled('search'), function ($builder) use ($request) {
                $search = '%' . $request->search . '%';

                $builder->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('title', 'like', $search)
                        ->orWhere('source_domain', 'like', $search)
                        ->orWhere('source_url', 'like', $search);
                });
            })
            ->latest('imported_at');

        $products = $query->paginate(12)->withQueryString();

        $labelGroups = DesignLabelGroup::query()
            ->whereIn('key', ['design_type', 'mood'])
            ->with(['labels' => fn ($labels) => $labels->where('is_active', true)->orderBy('sort_order')])
            ->get()
            ->keyBy('key');

        $stats = [
            'total' => DesignCatalogProduct::count(),
            'draft' => DesignCatalogProduct::where('status', 'draft')->count(),
            'active' => DesignCatalogProduct::where('status', 'active')->count(),
            'vectorized' => DesignCatalogProduct::where('vectorized', true)->count(),
        ];

        return view('backend.layout.DesignCatalog.index', [
            'products' => $products,
            'designTypes' => $labelGroups->get('design_type')?->labels ?? collect(),
            'moods' => $labelGroups->get('mood')?->labels ?? collect(),
            'stats' => $stats,
            'status' => $status,
        ]);
    }
}
