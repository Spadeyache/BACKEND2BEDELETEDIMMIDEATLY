<div class="container-xxl">
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span><span class="path2"></span>
                    </i>
                    <input type="text" data-kt-garment-table-filter="search"
                        class="form-control form-control-solid w-250px ps-13" placeholder="Search garment">
                </div>
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-garment-table-toolbar="base">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#kt_modal_add_garment">
                        <i class="ki-duotone ki-plus fs-2"></i> Add Garment
                    </button>
                </div>

                @include('backend.partials.garment.add')
            </div>
        </div>

        <div class="card-body py-4">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer"
                    id="kt_table_garments">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th class="min-w-150px">Name</th>
                            <th class="min-w-125px">Category</th>
                            <th class="min-w-100px">Blueprint ID</th>
                            <th class="min-w-100px">Display Order</th>
                            <th class="min-w-100px">Status</th>
                            <th class="min-w-125px">Created At</th>
                            <th class="text-end min-w-100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-600 fw-semibold"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
