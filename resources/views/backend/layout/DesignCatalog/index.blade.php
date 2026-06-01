@extends('backend.app')

@section('title', 'Design Catalog')

@push('style')
    <style>
        .design-catalog-page .catalog-stat {
            border: 1px solid #ece7df;
            border-radius: 8px;
            background: #fffaf4;
            padding: 16px;
        }

        .design-catalog-page .catalog-card {
            border: 1px solid #ece7df;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            height: 100%;
        }

        .design-catalog-page .catalog-image {
            width: 100%;
            aspect-ratio: 1 / 1;
            object-fit: cover;
            background: #f6f3ee;
        }

        .design-catalog-page .catalog-label {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: #f3eee6;
            color: #5f5448;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 8px;
            margin: 0 4px 6px 0;
        }

        .design-catalog-page .catalog-meta {
            color: #8a8178;
            font-size: 12px;
        }
    </style>
@endpush

@section('content')
    <div class="content fs-6 d-flex flex-column flex-column-fluid design-catalog-page" id="kt_content">
        <div class="toolbar" id="kt_toolbar">
            <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <h1 class="text-dark fw-bold my-1 fs-2">Design Catalog</h1>

                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item text-dark">Design Catalog</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            <div class="container-xxl">
                <div class="row g-4 mb-6">
                    <div class="col-sm-6 col-xl-3">
                        <div class="catalog-stat">
                            <div class="catalog-meta text-uppercase">Total imported</div>
                            <div class="fs-2 fw-bold text-dark">{{ $stats['total'] }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="catalog-stat">
                            <div class="catalog-meta text-uppercase">Draft</div>
                            <div class="fs-2 fw-bold text-dark">{{ $stats['draft'] }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="catalog-stat">
                            <div class="catalog-meta text-uppercase">Active</div>
                            <div class="fs-2 fw-bold text-dark">{{ $stats['active'] }}</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xl-3">
                        <div class="catalog-stat">
                            <div class="catalog-meta text-uppercase">Vectorized</div>
                            <div class="fs-2 fw-bold text-dark">{{ $stats['vectorized'] }}</div>
                        </div>
                    </div>
                </div>

                <div class="card mb-6">
                    <div class="card-body">
                        <form method="GET" action="{{ route('design-catalog.index') }}" class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Search</label>
                                <input type="text" name="search" class="form-control form-control-solid"
                                    value="{{ request('search') }}" placeholder="Title or source">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Status</label>
                                <select name="status" class="form-select form-select-solid">
                                    @foreach (['draft' => 'Draft', 'active' => 'Active', 'all' => 'All'] as $value => $label)
                                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Design Type</label>
                                <select name="design_type" class="form-select form-select-solid">
                                    <option value="">All design types</option>
                                    @foreach ($designTypes as $label)
                                        <option value="{{ $label->value }}" @selected(request('design_type') === $label->value)>
                                            {{ $label->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Mood</label>
                                <select name="mood" class="form-select form-select-solid">
                                    <option value="">All moods</option>
                                    @foreach ($moods as $label)
                                        <option value="{{ $label->value }}" @selected(request('mood') === $label->value)>
                                            {{ $label->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                                <a href="{{ route('design-catalog.index') }}" class="btn btn-light">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                @if ($products->isEmpty())
                    <div class="card">
                        <div class="card-body text-center py-12">
                            <div class="fs-4 fw-bold text-dark mb-2">No design products found</div>
                            <div class="text-muted">Try changing the status filter or importing more labeled products.</div>
                        </div>
                    </div>
                @else
                    <div class="row g-5">
                        @foreach ($products as $product)
                            @php
                                $image = $product->front_mockup_url ?: ($product->front_image_url ?: $product->back_image_url);
                                $visibleLabels = $product->labels->take(10);
                            @endphp

                            <div class="col-md-6 col-xl-4">
                                <div class="catalog-card">
                                    @if ($image)
                                        <img src="{{ $image }}" alt="{{ $product->title }}" class="catalog-image">
                                    @else
                                        <div class="catalog-image d-flex align-items-center justify-content-center text-muted">
                                            No image
                                        </div>
                                    @endif

                                    <div class="p-5">
                                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                            <div>
                                                <div class="fs-5 fw-bold text-dark">{{ $product->title }}</div>
                                                <div class="catalog-meta">{{ $product->source_domain ?? 'Unknown source' }}</div>
                                            </div>
                                            <span class="badge badge-light-{{ $product->status === 'active' ? 'success' : 'warning' }}">
                                                {{ ucfirst($product->status) }}
                                            </span>
                                        </div>

                                        <div class="row g-3 mb-4">
                                            <div class="col-6">
                                                <div class="catalog-meta">Design type</div>
                                                <div class="fw-semibold">{{ $product->design_type ?: '-' }}</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="catalog-meta">Mood</div>
                                                <div class="fw-semibold">{{ $product->mood ?: '-' }}</div>
                                            </div>
                                            <div class="col-6">
                                                <div class="catalog-meta">Confidence</div>
                                                <div class="fw-semibold">
                                                    {{ $product->label_confidence ? number_format($product->label_confidence * 100) . '%' : '-' }}
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="catalog-meta">Vectorized</div>
                                                <div class="fw-semibold">{{ $product->vectorized ? 'Yes' : 'No' }}</div>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            @foreach ($visibleLabels as $label)
                                                <span class="catalog-label">{{ $label->group?->name }}: {{ $label->name }}</span>
                                            @endforeach
                                            @if ($product->labels->count() > $visibleLabels->count())
                                                <span class="catalog-label">+{{ $product->labels->count() - $visibleLabels->count() }} more</span>
                                            @endif
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="catalog-meta">
                                                Imported {{ optional($product->imported_at)->format('M d, Y') ?? '-' }}
                                            </div>
                                            @if ($product->source_url)
                                                <a href="{{ $product->source_url }}" target="_blank" rel="noopener"
                                                    class="btn btn-sm btn-light-primary">Source</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
