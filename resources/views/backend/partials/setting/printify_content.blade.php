<div id="kt_account_settings_profile_details" class="show">
    <!--begin::Form-->
    <form action="{{ route('printify.store') }}" method="POST" enctype="multipart/form-data" class="form fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate">
        @csrf
        <!--begin::Card body-->
        <div class="card-body border-top p-9">
            <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                <div class="fv-row flex-row-fluid">
                    <label class="required form-label">Printify Bearer Token</label>
                    <input class="form-control form-control-solid" type="textarea" name="printify_bearer_token" placeholder="Enter Printify Bearer Token" value="{{ old('printify_bearer_token',  env('PRINTIFY_BEARER_TOKEN') ?? '') }}">
                </div>
            </div>

            {{-- <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                <div class="fv-row flex-row-fluid">
                    <label class="required form-label">Printify Shop Id</label>
                    <input class="form-control form-control-solid" type="text" name="printify_shop_id" placeholder="Enter Printify Shop Id" value="{{ old('printify_shop_id',  env('PRINTIFY_SHOP_ID') ?? '') }}">
                </div>
            </div> --}}
        </div>
        <!--end::Card body-->

        <!--begin::Actions-->
        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <button type="submit" class="btn btn-primary" id="kt_account_profile_details_submit">Save Changes</button>
        </div>
        <!--end::Actions-->
        <input type="hidden"></form>
    <!--end::Form-->
</div>
