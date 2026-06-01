<div class="container-xxl">
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <input type="text" data-kt-variant-table-filter="search"
                        class="form-control form-control-solid w-250px ps-13" placeholder="Search variant">
                </div>
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-variant-table-toolbar="base">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#kt_modal_add_variant">
                        <i class="ki-duotone ki-plus fs-2"></i> Add Variant
                    </button>
                </div>

                @include('backend.partials.garment_variant.add')
            </div>
        </div>

        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer"
                    id="kt_table_variants">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-150px">Garment</th>
                            <th class="min-w-80px">Size</th>
                            <th class="min-w-100px">Color</th>
                            <th class="min-w-100px">Mockup</th>
                            <th class="min-w-100px">Price</th>
                            <th class="min-w-80px">Status</th>
                            <th class="min-w-80px">Order</th>
                            <th class="text-end min-w-100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
