{{-- ======================== ADD MODAL ======================== --}}
<div class="modal fade" id="kt_modal_add_variant" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_add_variant_header">
                <h2 class="fw-bold">Add Garment Variant</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body px-5 my-7">
                <form action="{{ route('garment-variant.store') }}" method="POST" enctype="multipart/form-data"
                      class="form fv-plugins-bootstrap5 fv-plugins-framework">
                    @csrf
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" data-kt-scroll="true"
                         data-kt-scroll-activate="true" data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#kt_modal_add_variant_header"
                         data-kt-scroll-offset="300px" style="max-height: 500px;">

                        {{-- Garment Dropdown --}}
                        <div class="fv-row mb-7">
                            <label class="required form-label">Garment</label>
                            <select class="form-select form-select-solid" name="garment_id" id="add_garment_id" required>
                                <option value="">— Select Garment —</option>
                                @foreach($garments as $garment)
                                    <option value="{{ $garment->id }}">{{ $garment->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Printify Variant ID --}}
                        <div class="fv-row mb-7">
                            <label class="required form-label">Printify Variant ID</label>
                            <select class="form-select form-select-solid" name="printify_variant_id" id="add_printify_variant_id" data-control="select2" data-placeholder="— Select Variant —" data-dropdown-parent="#kt_modal_add_variant" required>
                                <option value="">— Select Variant —</option>
                            </select>
                        </div>

                        {{-- Size & Color --}}
                        <div class="row mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Size</label>
                                <input class="form-control form-control-solid" type="text" name="size"
                                       value="{{ old('size') }}" placeholder="e.g. M, L, XL" required>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Color</label>
                                <input class="form-control form-control-solid" type="text" name="color"
                                       value="{{ old('color') }}" placeholder="e.g. Black" required>
                            </div>
                        </div>

                        {{-- Color Hex & Price --}}
                        <div class="row mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="form-label">Color Hex</label>
                                <input class="form-control form-control-solid" type="text" name="color_hex"
                                       value="{{ old('color_hex') }}" placeholder="e.g. #000000">
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Price (cents)</label>
                                <input class="form-control form-control-solid" type="number" name="price_cents"
                                       value="{{ old('price_cents') }}" min="0" placeholder="e.g. 2999" required>
                            </div>
                        </div>

                        {{-- Display Order & Enabled --}}
                        <div class="row mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Display Order</label>
                                <input class="form-control form-control-solid" type="number" name="display_order"
                                       value="{{ old('display_order', 0) }}" min="0" required>
                            </div>
                            <div class="col-md-6 fv-row d-flex align-items-end pb-1">
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="is_enabled"
                                           id="add_is_enabled" value="1" checked>
                                    <label class="form-check-label fw-semibold text-gray-600" for="add_is_enabled">Enabled</label>
                                </div>
                            </div>
                        </div>

                        {{-- Blank Mockup Image --}}
                        <div class="fv-row mb-7">
                            <label class="required d-block fw-semibold fs-6 mb-5">Blank Mockup Image</label>
                            <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                <div class="image-input-wrapper w-125px h-125px"
                                     style="background-image: url({{ asset('backend/assets/media/svg/files/blank-image.svg') }});"></div>
                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                       data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change image">
                                    <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                    <input type="file" name="blank_mockup_url" accept=".png,.jpg,.jpeg,.webp,.svg" required>
                                    <input type="hidden" name="blank_mockup_url_remove">
                                </label>
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                      data-kt-image-input-action="cancel">
                                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </span>
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                      data-kt-image-input-action="remove">
                                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </span>
                            </div>
                            <div class="form-text">Allowed: png, jpg, jpeg, webp, svg. Max 5MB.</div>
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
<div class="modal fade" id="kt_modal_edit_variant" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_edit_variant_header">
                <h2 class="fw-bold">Edit Garment Variant</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body px-5 my-7">
                <form action="#" method="POST" enctype="multipart/form-data"
                      class="form fv-plugins-bootstrap5 fv-plugins-framework" id="kt_modal_edit_variant_form">
                    @csrf
                    @method('PUT')
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" data-kt-scroll="true"
                         data-kt-scroll-activate="true" data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#kt_modal_edit_variant_header"
                         data-kt-scroll-offset="300px" style="max-height: 500px;">

                        {{-- Garment Dropdown --}}
                        <div class="fv-row mb-7">
                            <label class="required form-label">Garment</label>
                            <select class="form-select form-select-solid" name="garment_id" id="edit_garment_id" required>
                                <option value="">— Select Garment —</option>
                                @foreach($garments as $garment)
                                    <option value="{{ $garment->id }}">{{ $garment->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Printify Variant ID --}}
                        <div class="fv-row mb-7">
                            <label class="required form-label">Printify Variant ID</label>
                            <select class="form-select form-select-solid" name="printify_variant_id" id="edit_printify_variant_id" data-control="select2" data-placeholder="— Select Variant —" data-dropdown-parent="#kt_modal_edit_variant" required>
                                <option value="">— Select Variant —</option>
                            </select>
                        </div>

                        {{-- Size & Color --}}
                        <div class="row mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Size</label>
                                <input class="form-control form-control-solid" type="text" name="size"
                                       id="edit_size" placeholder="e.g. M, L, XL" required>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Color</label>
                                <input class="form-control form-control-solid" type="text" name="color"
                                       id="edit_color" placeholder="e.g. Black" required>
                            </div>
                        </div>

                        {{-- Color Hex & Price --}}
                        <div class="row mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="form-label">Color Hex</label>
                                <input class="form-control form-control-solid" type="text" name="color_hex"
                                       id="edit_color_hex" placeholder="e.g. #000000">
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Price (cents)</label>
                                <input class="form-control form-control-solid" type="number" name="price_cents"
                                       id="edit_price_cents" min="0" placeholder="e.g. 2999" required>
                            </div>
                        </div>

                        {{-- Display Order & Enabled --}}
                        <div class="row mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required form-label">Display Order</label>
                                <input class="form-control form-control-solid" type="number" name="display_order"
                                       id="edit_display_order" min="0" required>
                            </div>
                            <div class="col-md-6 fv-row d-flex align-items-end pb-1">
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="is_enabled"
                                           id="edit_is_enabled" value="1">
                                    <label class="form-check-label fw-semibold text-gray-600" for="edit_is_enabled">Enabled</label>
                                </div>
                            </div>
                        </div>

                        {{-- Blank Mockup Image --}}
                        <div class="fv-row mb-7">
                            <label class="d-block fw-semibold fs-6 mb-5">Blank Mockup Image</label>
                            <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                <div class="image-input-wrapper w-125px h-125px" id="edit_blank_mockup_preview"></div>
                                <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                       data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change image">
                                    <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                    <input type="file" name="blank_mockup_url" accept=".png,.jpg,.jpeg,.webp,.svg">
                                    <input type="hidden" name="blank_mockup_url_remove">
                                </label>
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                      data-kt-image-input-action="cancel">
                                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </span>
                                <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                      data-kt-image-input-action="remove">
                                    <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </span>
                            </div>
                            <div class="form-text">Leave empty to keep current image. Allowed: png, jpg, jpeg, webp, svg.</div>
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
