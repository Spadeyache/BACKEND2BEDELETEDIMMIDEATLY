<div class=" container-xxl " data-select2-id="select2-data-198-e71y">
    <div class="card" data-select2-id="select2-data-197-m5it">
        <div class="card-header border-0 pt-6" data-select2-id="select2-data-196-qcil">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5"><span class="path1"></span><span
                            class="path2"></span></i> <input type="text" data-kt-product-table-filter="search"
                        class="form-control form-control-solid w-250px ps-13" placeholder="Search product">
                </div>
            </div>

            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-product-table-toolbar="base"
                    data-select2-id="select2-data-195-9exk">

                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#kt_modal_add_product">
                        <i class="ki-duotone ki-plus fs-2"></i> Add Product
                    </button>
                </div>

                <div class="d-flex justify-content-end align-items-center d-none"
                    data-kt-product-table-toolbar="selected">
                    <div class="fw-bold me-5">
                        <span class="me-2" data-kt-product-table-select="selected_count"></span> Selected
                    </div>

                    <button type="button" class="btn btn-danger" data-kt-product-table-select="delete_selected">
                        Delete Selected
                    </button>
                </div>

                @include('backend.partials.veara_product.add')
            </div>
        </div>

        <div class="card-body py-4">

            <div id="kt_table_products_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable no-footer"
                        id="kt_table_products">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_table_products"
                                    rowspan="1" colspan="1"
                                    aria-label="Image: activate to sort column ascending" style="width: 161.844px;">
                                    Image</th>
                                <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_table_products"
                                    rowspan="1" colspan="1"
                                    aria-label="Product ID: activate to sort column ascending" style="width: 278.328px;">
                                    Product ID</th>
                                <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_table_products"
                                    rowspan="1" colspan="1"
                                    aria-label="Design Type: activate to sort column ascending" style="width: 161.844px;">
                                    Design Type</th>
                                <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_table_products"
                                    rowspan="1" colspan="1"
                                    aria-label="Category: activate to sort column ascending" style="width: 161.844px;">
                                    Category</th>
                                <th class="min-w-125px sorting" tabindex="0" aria-controls="kt_table_products"
                                    rowspan="1" colspan="1"
                                    aria-label="Created At: activate to sort column ascending"
                                    style="width: 210.266px;">Created At</th>
                                <th class="text-end min-w-100px sorting_disabled" rowspan="1" colspan="1"
                                    aria-label="Actions" style="width: 132.484px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <tr class="odd">

                            </tr>
                        </tbody>
                    </table>

                </div>

            </div>
        </div>
    </div>
</div>
