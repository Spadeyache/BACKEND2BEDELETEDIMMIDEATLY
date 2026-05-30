<div class="modal fade" id="kt_modal_add_product" tabindex="-1" style="display: none;"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_add_product_header">
                <h2 class="fw-bold">Add Veara Product</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary"
                     data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>

            <div class="modal-body px-5 my-7">
                <form action="{{ route('veara-product.store') }}" method="POST" enctype="multipart/form-data" class="form fv-plugins-bootstrap5 fv-plugins-framework" >
                    @csrf
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10"
                         id="kt_modal_add_product_scroll" data-kt-scroll="true"
                         data-kt-scroll-activate="true" data-kt-scroll-max-height="auto"
                         data-kt-scroll-dependencies="#kt_modal_add_product_header"
                         data-kt-scroll-wrappers="#kt_modal_add_product_scroll"
                         data-kt-scroll-offset="300px" style="max-height: 400px;">
                        
                        <div class="fv-row mb-7">
                            <label class="required form-label">Product ID</label>
                            <input class="form-control form-control-solid" type="text" name="product_id" value="{{ old('product_id') }}" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Title</label>
                            <input class="form-control form-control-solid" type="text" name="title" value="{{ old('title') }}" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Price</label>
                            <input class="form-control form-control-solid" type="number" step="0.01" name="price" value="{{ old('price') }}" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="form-label">Description</label>
                            <textarea class="form-control form-control-solid" name="description" rows="3">{{ old('description') }}</textarea>
                        </div>

                        <!--begin::Image placeholder style-->
                        <style>
                            .image-input-placeholder {
                                background-image: url({{ asset('backend/assets/media/svg/files/blank-image.svg') }});
                            }

                            [data-bs-theme="dark"] .image-input-placeholder {
                                background-image: url({{ asset('backend/assets/media/svg/files/blank-image-dark.svg') }});
                            }
                        </style>
                        <!--end::Image placeholder style-->

                        <div class="row mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required d-block fw-semibold fs-6 mb-5">Veara Front Image</label>
                                <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-125px h-125px" style="background-image: url({{ asset('backend/assets/media/svg/files/blank-image.svg') }});"></div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change image" data-bs-original-title="Change image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="veara_front" accept=".png, .jpg, .jpeg, .webp, .svg" required>
                                        <input type="hidden" name="veara_front_remove">
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" aria-label="Cancel image" data-bs-original-title="Cancel image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" aria-label="Remove image" data-bs-original-title="Remove image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                </div>
                                <div class="form-text">Allowed file types: png, jpg, jpeg, webp, svg.</div>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required d-block fw-semibold fs-6 mb-5">Veara Back Image</label>
                                <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-125px h-125px" style="background-image: url({{ asset('backend/assets/media/svg/files/blank-image.svg') }});"></div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change image" data-bs-original-title="Change image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="veara_back" accept=".png, .jpg, .jpeg, .webp, .svg" required>
                                        <input type="hidden" name="veara_back_remove">
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" aria-label="Cancel image" data-bs-original-title="Cancel image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" aria-label="Remove image" data-bs-original-title="Remove image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                </div>
                                <div class="form-text">Allowed file types: png, jpg, jpeg, webp, svg.</div>
                            </div>
                        </div>

                        <div class="row mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="required d-block fw-semibold fs-6 mb-5">Front Mockup</label>
                                <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-125px h-125px" style="background-image: url({{ asset('backend/assets/media/svg/files/blank-image.svg') }});"></div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change image" data-bs-original-title="Change image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="front_mockup" accept=".png, .jpg, .jpeg, .webp, .svg" required>
                                        <input type="hidden" name="front_mockup_remove">
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" aria-label="Cancel image" data-bs-original-title="Cancel image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" aria-label="Remove image" data-bs-original-title="Remove image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                </div>
                                <div class="form-text">Allowed file types: png, jpg, jpeg, webp, svg.</div>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="required d-block fw-semibold fs-6 mb-5">Back Mockup</label>
                                <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-125px h-125px" style="background-image: url({{ asset('backend/assets/media/svg/files/blank-image.svg') }});"></div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change image" data-bs-original-title="Change image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="back_mockup" accept=".png, .jpg, .jpeg, .webp, .svg" required>
                                        <input type="hidden" name="back_mockup_remove">
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" aria-label="Cancel image" data-bs-original-title="Cancel image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" aria-label="Remove image" data-bs-original-title="Remove image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                </div>
                                <div class="form-text">Allowed file types: png, jpg, jpeg, webp, svg.</div>
                            </div>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Style Tags</label>
                            <input class="form-control form-control-solid" type="text" name="style_tags" placeholder="e.g. Vintage, Modern" value="{{ old('style_tags') }}" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Color Palette (JSON/Array format)</label>
                            <input class="form-control form-control-solid" type="text" name="color_palette" placeholder='["#ffffff", "#000000"]' value="{{ old('color_palette') }}" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Design Type</label>
                            <input class="form-control form-control-solid" type="text" name="design_type" value="{{ old('design_type') }}" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Category</label>
                            <input class="form-control form-control-solid" type="text" name="category" value="{{ old('category') }}" placeholder="e.g. hoodie, t-shirt" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Subject Matter</label>
                            <input class="form-control form-control-solid" type="text" name="subject_matter" value="{{ old('subject_matter') }}" required>
                        </div>

                        <div class="row mb-7">
                            <div class="col-md-4">
                                <label class="required form-label">Mood</label>
                                <input class="form-control form-control-solid" type="text" name="mood" value="{{ old('mood') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="required form-label">Complexity Score</label>
                                <input class="form-control form-control-solid" type="number" name="complexity_score" value="{{ old('complexity_score') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="required form-label">Pet Relevance Score</label>
                                <input class="form-control form-control-solid" type="number" step="0.01" name="pet_relevance_score" value="{{ old('pet_relevance_score') }}" required>
                            </div>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Target Audience Guess</label>
                            <input class="form-control form-control-solid" type="text" name="target_audience_guess" value="{{ old('target_audience_guess') }}" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Seasonal Fit</label>
                            <input class="form-control form-control-solid" type="text" name="seasonal_fit" value="{{ old('seasonal_fit') }}" required>
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

<!-- Edit Product Modal -->
<div class="modal fade" id="kt_modal_edit_product" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_edit_product_header">
                <h2 class="fw-bold">Edit Veara Product</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body px-5 my-7">
                <form action="#" method="POST" enctype="multipart/form-data" class="form fv-plugins-bootstrap5 fv-plugins-framework" id="kt_modal_edit_product_form">
                    @csrf
                    @method('PUT')
                    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_edit_product_scroll" data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_edit_product_header" data-kt-scroll-wrappers="#kt_modal_edit_product_scroll" data-kt-scroll-offset="300px" style="max-height: 400px;">
                        
                        <div class="fv-row mb-7">
                            <label class="required form-label">Product ID</label>
                            <input class="form-control form-control-solid" type="text" name="product_id" id="edit_product_id" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Title</label>
                            <input class="form-control form-control-solid" type="text" name="title" id="edit_title" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Price</label>
                            <input class="form-control form-control-solid" type="number" step="0.01" name="price" id="edit_price" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="form-label">Description</label>
                            <textarea class="form-control form-control-solid" name="description" id="edit_description" rows="3"></textarea>
                        </div>

                        <div class="row mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="d-block fw-semibold fs-6 mb-5">Veara Front Image</label>
                                <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-125px h-125px" id="edit_veara_front_preview"></div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change image" data-bs-original-title="Change image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="veara_front" accept=".png, .jpg, .jpeg, .webp, .svg">
                                        <input type="hidden" name="veara_front_remove">
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" aria-label="Cancel image" data-bs-original-title="Cancel image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" aria-label="Remove image" data-bs-original-title="Remove image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                </div>
                                <div class="form-text">Allowed file types: png, jpg, jpeg, webp, svg.</div>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="d-block fw-semibold fs-6 mb-5">Veara Back Image</label>
                                <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-125px h-125px" id="edit_veara_back_preview"></div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change image" data-bs-original-title="Change image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="veara_back" accept=".png, .jpg, .jpeg, .webp, .svg">
                                        <input type="hidden" name="veara_back_remove">
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" aria-label="Cancel image" data-bs-original-title="Cancel image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" aria-label="Remove image" data-bs-original-title="Remove image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                </div>
                                <div class="form-text">Allowed file types: png, jpg, jpeg, webp, svg.</div>
                            </div>
                        </div>

                        <div class="row mb-7">
                            <div class="col-md-6 fv-row">
                                <label class="d-block fw-semibold fs-6 mb-5">Front Mockup</label>
                                <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-125px h-125px" id="edit_front_mockup_preview"></div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change image" data-bs-original-title="Change image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="front_mockup" accept=".png, .jpg, .jpeg, .webp, .svg">
                                        <input type="hidden" name="front_mockup_remove">
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" aria-label="Cancel image" data-bs-original-title="Cancel image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" aria-label="Remove image" data-bs-original-title="Remove image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                </div>
                                <div class="form-text">Allowed file types: png, jpg, jpeg, webp, svg.</div>
                            </div>
                            <div class="col-md-6 fv-row">
                                <label class="d-block fw-semibold fs-6 mb-5">Back Mockup</label>
                                <div class="image-input image-input-outline image-input-placeholder" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-125px h-125px" id="edit_back_mockup_preview"></div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" aria-label="Change image" data-bs-original-title="Change image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-pencil fs-7"><span class="path1"></span><span class="path2"></span></i>
                                        <input type="file" name="back_mockup" accept=".png, .jpg, .jpeg, .webp, .svg">
                                        <input type="hidden" name="back_mockup_remove">
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" aria-label="Cancel image" data-bs-original-title="Cancel image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" aria-label="Remove image" data-bs-original-title="Remove image" data-kt-initialized="1">
                                        <i class="ki-duotone ki-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                </div>
                                <div class="form-text">Allowed file types: png, jpg, jpeg, webp, svg.</div>
                            </div>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Style Tags</label>
                            <input class="form-control form-control-solid" type="text" name="style_tags" id="edit_style_tags" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Color Palette</label>
                            <input class="form-control form-control-solid" type="text" name="color_palette" id="edit_color_palette" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Design Type</label>
                            <input class="form-control form-control-solid" type="text" name="design_type" id="edit_design_type" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Category</label>
                            <input class="form-control form-control-solid" type="text" name="category" id="edit_category" placeholder="e.g. hoodie, t-shirt" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Subject Matter</label>
                            <input class="form-control form-control-solid" type="text" name="subject_matter" id="edit_subject_matter" required>
                        </div>

                        <div class="row mb-7">
                            <div class="col-md-4">
                                <label class="required form-label">Mood</label>
                                <input class="form-control form-control-solid" type="text" name="mood" id="edit_mood" required>
                            </div>
                            <div class="col-md-4">
                                <label class="required form-label">Complexity Score</label>
                                <input class="form-control form-control-solid" type="number" name="complexity_score" id="edit_complexity_score" required>
                            </div>
                            <div class="col-md-4">
                                <label class="required form-label">Pet Relevance Score</label>
                                <input class="form-control form-control-solid" type="number" step="0.01" name="pet_relevance_score" id="edit_pet_relevance_score" required>
                            </div>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Target Audience Guess</label>
                            <input class="form-control form-control-solid" type="text" name="target_audience_guess" id="edit_target_audience_guess" required>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required form-label">Seasonal Fit</label>
                            <input class="form-control form-control-solid" type="text" name="seasonal_fit" id="edit_seasonal_fit" required>
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
