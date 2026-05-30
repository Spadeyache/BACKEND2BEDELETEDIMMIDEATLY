{{-- ======================== ADD MODAL ======================== --}}
<div class="modal fade" id="kt_modal_add_garment" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-700px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_add_garment_header">
                <h2 class="fw-bold">Add Garment</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body px-5 my-7">
                <form action="{{ route('garment.store') }}" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework">
                    @csrf
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" data-kt-scroll="true"
                         data-kt-scroll-activate="true" data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#kt_modal_add_garment_header"
                         data-kt-scroll-wrappers="#kt_modal_add_garment_scroll"
                         data-kt-scroll-offset="300px" style="max-height: 450px;">

                        <div class="fv-row mb-7">
                            <label class="required form-label">Name</label>
                            <input class="form-control form-control-solid" type="text" name="name" value="{{ old('name') }}" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="form-label">Description</label>
                            <textarea class="form-control form-control-solid" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="row mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Category</label>
                                <input class="form-control form-control-solid" type="text" name="category" value="{{ old('category') }}" placeholder="e.g. hoodie, t-shirt" required>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Display Order</label>
                                <input class="form-control form-control-solid" type="number" name="display_order" value="{{ old('display_order', 0) }}" min="0" required>
                            </div>
                        </div>

                        <div class="row mb-7">
                            {{-- Blueprint Selector --}}
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Blueprint</label>
                                <div class="d-flex gap-2 mb-2">
                                    <select class="form-select form-select-solid flex-grow-1" name="blueprint_id" id="add_blueprint_select" required>
                                        <option value="">— Select Blueprint —</option>
                                        @foreach($blueprints as $bp)
                                            <option value="{{ $bp['id'] }}" 
                                                    data-brand="{{ $bp['brand'] ?? '' }}" 
                                                    data-model="{{ $bp['model'] ?? '' }}" 
                                                    data-description="{{ htmlspecialchars($bp['description'] ?? '') }}"
                                                    data-images="{{ json_encode($bp['images'] ?? []) }}">
                                                {{ $bp['title'] }} (ID: {{ $bp['id'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-light-info btn-sm px-3" id="add_blueprint_details_btn" style="white-space: nowrap;">
                                        Details
                                    </button>
                                </div>
                                
                                {{-- Collapse Panel for Blueprint --}}
                                <div class="collapse mt-2 border border-dashed border-gray-300 rounded p-4 bg-light-lighten" id="add_blueprint_details_panel">
                                    <h5 class="fw-bold mb-1 text-gray-800" id="add_bp_title_disp"></h5>
                                    <div class="text-muted fs-7 mb-2">
                                        <span class="badge badge-light-primary me-2">Brand: <span id="add_bp_brand_disp">—</span></span>
                                        <span class="badge badge-light-success">Model: <span id="add_bp_model_disp">—</span></span>
                                    </div>
                                    
                                    {{-- Images Grid --}}
                                    <div class="d-flex gap-2 overflow-auto py-2 mb-3 scrollbar-sm" id="add_bp_images_disp" style="max-height: 100px;">
                                        <!-- Images will be dynamically inserted here -->
                                    </div>

                                    <div class="fs-7 text-gray-600 mh-150px pr-2" id="add_bp_desc_disp" style="max-height: 150px; overflow-y: auto;">
                                    </div>
                                </div>
                            </div>

                            {{-- Print Provider Selector --}}
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Print Provider</label>
                                <div class="d-flex gap-2 mb-2">
                                    <select class="form-select form-select-solid flex-grow-1" name="print_provider_id" id="add_provider_select" required>
                                        <option value="">— Select Print Provider —</option>
                                    </select>
                                    <button type="button" class="btn btn-light-info btn-sm px-3" id="add_provider_details_btn" style="white-space: nowrap;">
                                        Details
                                    </button>
                                </div>
                                
                                {{-- Collapse Panel for Print Provider --}}
                                <div class="collapse mt-2 border border-dashed border-gray-300 rounded p-4 bg-light-lighten" id="add_provider_details_panel">
                                    <h5 class="fw-bold mb-1 text-gray-800" id="add_prov_title_disp"></h5>
                                    <div class="fs-7 text-gray-600">
                                        <div><strong>Address:</strong> <span id="add_prov_address_disp">—</span></div>
                                        <div><strong>Location:</strong> <span id="add_prov_city_disp">—</span>, <span id="add_prov_region_disp">—</span> <span id="add_prov_zip_disp"></span>, <span id="add_prov_country_disp">—</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="fv-row mb-7">
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="is_active" id="add_is_active" value="1" checked>
                                <label class="form-check-label fw-semibold text-gray-600" for="add_is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="text-center pt-10">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Submit</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ======================== EDIT MODAL ======================== --}}
<div class="modal fade" id="kt_modal_edit_garment" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-700px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_edit_garment_header">
                <h2 class="fw-bold">Edit Garment</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body px-5 my-7">
                <form action="#" method="POST" class="form fv-plugins-bootstrap5 fv-plugins-framework" id="kt_modal_edit_garment_form">
                    @csrf
                    @method('PUT')
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" data-kt-scroll="true"
                         data-kt-scroll-activate="true" data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#kt_modal_edit_garment_header"
                         data-kt-scroll-wrappers="#kt_modal_edit_garment_scroll"
                         data-kt-scroll-offset="300px" style="max-height: 450px;">

                        <div class="fv-row mb-7">
                            <label class="required form-label">Name</label>
                            <input class="form-control form-control-solid" type="text" name="name" id="edit_name" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="form-label">Description</label>
                            <textarea class="form-control form-control-solid" name="description" id="edit_description" rows="3"></textarea>
                        </div>

                        <div class="row mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Category</label>
                                <input class="form-control form-control-solid" type="text" name="category" id="edit_category" placeholder="e.g. hoodie, t-shirt" required>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Display Order</label>
                                <input class="form-control form-control-solid" type="number" name="display_order" id="edit_display_order" min="0" required>
                            </div>
                        </div>

                        <div class="row mb-7">
                            {{-- Blueprint Selector --}}
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Blueprint</label>
                                <div class="d-flex gap-2 mb-2">
                                    <select class="form-select form-select-solid flex-grow-1" name="blueprint_id" id="edit_blueprint_select" required>
                                        <option value="">— Select Blueprint —</option>
                                        @foreach($blueprints as $bp)
                                            <option value="{{ $bp['id'] }}" 
                                                    data-brand="{{ $bp['brand'] ?? '' }}" 
                                                    data-model="{{ $bp['model'] ?? '' }}" 
                                                    data-description="{{ htmlspecialchars($bp['description'] ?? '') }}"
                                                    data-images="{{ json_encode($bp['images'] ?? []) }}">
                                                {{ $bp['title'] }} (ID: {{ $bp['id'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-light-info btn-sm px-3" id="edit_blueprint_details_btn" style="white-space: nowrap;">
                                        Details
                                    </button>
                                </div>
                                
                                {{-- Collapse Panel for Blueprint --}}
                                <div class="collapse mt-2 border border-dashed border-gray-300 rounded p-4 bg-light-lighten" id="edit_blueprint_details_panel">
                                    <h5 class="fw-bold mb-1 text-gray-800" id="edit_bp_title_disp"></h5>
                                    <div class="text-muted fs-7 mb-2">
                                        <span class="badge badge-light-primary me-2">Brand: <span id="edit_bp_brand_disp">—</span></span>
                                        <span class="badge badge-light-success">Model: <span id="edit_bp_model_disp">—</span></span>
                                    </div>
                                    
                                    {{-- Images Grid --}}
                                    <div class="d-flex gap-2 overflow-auto py-2 mb-3 scrollbar-sm" id="edit_bp_images_disp" style="max-height: 100px;">
                                        <!-- Images will be dynamically inserted here -->
                                    </div>

                                    <div class="fs-7 text-gray-600 mh-150px pr-2" id="edit_bp_desc_disp" style="max-height: 150px; overflow-y: auto;">
                                    </div>
                                </div>
                            </div>

                            {{-- Print Provider Selector --}}
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Print Provider</label>
                                <div class="d-flex gap-2 mb-2">
                                    <select class="form-select form-select-solid flex-grow-1" name="print_provider_id" id="edit_provider_select" required>
                                        <option value="">— Select Print Provider —</option>
                                    </select>
                                    <button type="button" class="btn btn-light-info btn-sm px-3" id="edit_provider_details_btn" style="white-space: nowrap;">
                                        Details
                                    </button>
                                </div>
                                
                                {{-- Collapse Panel for Print Provider --}}
                                <div class="collapse mt-2 border border-dashed border-gray-300 rounded p-4 bg-light-lighten" id="edit_provider_details_panel">
                                    <h5 class="fw-bold mb-1 text-gray-800" id="edit_prov_title_disp"></h5>
                                    <div class="fs-7 text-gray-600">
                                        <div><strong>Address:</strong> <span id="edit_prov_address_disp">—</span></div>
                                        <div><strong>Location:</strong> <span id="edit_prov_city_disp">—</span>, <span id="edit_prov_region_disp">—</span> <span id="edit_prov_zip_disp"></span>, <span id="edit_prov_country_disp">—</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="fv-row mb-7">
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active" value="1">
                                <label class="form-check-label fw-semibold text-gray-600" for="edit_is_active">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="text-center pt-10">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
