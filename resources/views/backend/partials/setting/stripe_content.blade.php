<div id="kt_account_settings_profile_details" class="show">
    <!--begin::Form-->
    <form action="{{ route('stripe.store') }}" method="POST" enctype="multipart/form-data" class="form fv-plugins-bootstrap5 fv-plugins-framework" novalidate="novalidate">
        @csrf
        <!--begin::Card body-->
        <div class="card-body border-top p-9">
            <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                <div class="fv-row flex-row-fluid">
                    <label class="required form-label">Stripe Key</label>
                    <input class="form-control form-control-solid" type="textarea" name="stripe_key" placeholder="Enter Stripe Key" value="{{ old('stripe_key',  env('STRIPE_KEY') ?? '') }}">
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                <div class="fv-row flex-row-fluid">
                    <label class="required form-label">Stripe Secret</label>
                    <input class="form-control form-control-solid" type="text" name="stripe_secret" placeholder="Enter Stripe Secret" value="{{ old('stripe_secret',  env('STRIPE_SECRET') ?? '') }}">
                </div>
            </div>

            <!-- Third Row: Stripe Configuration (Continued) -->
            <div class="d-flex flex-column flex-md-row gap-5 mb-6">
                <div class="fv-row flex-row-fluid">
                    <label class="required form-label">Stripe Webhook Secret</label>
                    <input class="form-control form-control-solid" type="text" name="stripe_webhook_secret" placeholder="Enter Stripe Webhook Secret" value="{{ old('stripe_webhook_secret',  env('STRIPE_WEBHOOK_SECRET') ?? '') }}">
                </div>
            </div>
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
