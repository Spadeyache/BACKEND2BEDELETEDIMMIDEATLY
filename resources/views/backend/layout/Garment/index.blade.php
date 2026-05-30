@extends('backend.app')

@section('title', 'List of Garments')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_filter { display: none !important; }
    </style>
@endpush

@section('content')
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="toolbar" id="kt_toolbar">
            <div class="container-fluid d-flex flex-stack flex-wrap flex-sm-nowrap">
                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <h1 class="text-dark fw-bold my-1 fs-2">List of Garments</h1>
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item text-dark">Garments</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            @include('backend.partials.garment.index_main')
        </div>

        @push('script')
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

            <script>
                $('input[data-kt-garment-table-filter="search"]').on('keyup', function () {
                    $('#kt_table_garments').DataTable().search(this.value).draw();
                });

                $(document).ready(function () {
                    $('#kt_table_garments').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('garment.index') }}",
                            type: "get",
                        },
                        columns: [
                            { data: 'name',           name: 'name' },
                            { data: 'category',       name: 'category' },
                            { data: 'blueprint_id',   name: 'blueprint_id' },
                            { data: 'display_order',  name: 'display_order' },
                            { data: 'is_active',      name: 'is_active', orderable: false, searchable: false },
                            { data: 'created_at',     name: 'created_at' },
                            { data: 'actions',        name: 'actions', orderable: false, searchable: false },
                        ],
                        drawCallback: function () {
                            if (typeof KTMenu !== 'undefined') KTMenu.createInstances();
                        }
                    });
                });

                // Edit modal open
                $(document).on('click', '.edit-garment-btn', function (e) {
                    e.preventDefault();
                    const id = $(this).data('id');
                    $.ajax({
                        url: "{{ route('garment.edit', ['id' => '__id__']) }}".replace('__id__', id),
                        type: "GET",
                        success: function (response) {
                            const g = response.garment;
                            $('#kt_modal_edit_garment_form').attr('action', "{{ route('garment.update', ['id' => '__id__']) }}".replace('__id__', id));
                            $('#edit_name').val(g.name);
                            $('#edit_description').val(g.description);
                            $('#edit_category').val(g.category);
                            
                            // Set selectors & trigger changes in background
                            $('#edit_blueprint_select').val(g.blueprint_id).trigger('change', [g.print_provider_id]);

                            // Collapse details panels by default on open
                            $('#edit_blueprint_details_panel').removeClass('show');
                            $('#edit_provider_details_panel').removeClass('show');

                            $('#edit_display_order').val(g.display_order);
                            $('#edit_is_active').prop('checked', g.is_active == 1);
                            $('#kt_modal_edit_garment').modal('show');
                        },
                        error: function (xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to fetch data.', 'error');
                        }
                    });
                });

                // --- Interactive blueprint and provider details logic ---
                
                function loadPrintProviders(blueprintId, targetSelect, selectedProviderId = null) {
                    if (!blueprintId) {
                        $(targetSelect).html('<option value="">— Select Print Provider —</option>');
                        if ($(targetSelect).hasClass('select2-hidden-accessible')) $(targetSelect).trigger('change');
                        return;
                    }
                    const url = "{{ route('garment.print-providers', ['id' => '__id__']) }}".replace('__id__', blueprintId);
                    $(targetSelect).html('<option value="">Loading...</option>');
                    $.getJSON(url, function (resp) {
                        let options = '<option value="">— Select Print Provider —</option>';
                        if (resp.status === 'success' && resp.providers) {
                            resp.providers.forEach(p => {
                                const selected = selectedProviderId == p.id ? 'selected' : '';
                                const loc = p.location || {};
                                options += `<option value="${p.id}" ${selected} 
                                    data-city="${loc.city || ''}" 
                                    data-country="${loc.country || ''}" 
                                    data-region="${loc.region || ''}" 
                                    data-zip="${loc.zip || ''}" 
                                    data-address="${loc.address1 || ''}">
                                    ${p.title} (ID: ${p.id})
                                </option>`;
                            });
                        }
                        $(targetSelect).html(options);
                        if ($(targetSelect).hasClass('select2-hidden-accessible')) {
                            $(targetSelect).trigger('change');
                        } else {
                            $(targetSelect).select2({ dropdownParent: $(targetSelect).closest('.modal') });
                        }
                    }).fail(function() {
                        $(targetSelect).html('<option value="">— Select Print Provider —</option>');
                    });
                }

                $('#add_provider_select, #edit_provider_select').on('select2:opening', function (e) {
                    const blueprintSelect = $(this).attr('id') === 'add_provider_select' ? '#add_blueprint_select' : '#edit_blueprint_select';
                    if (!$(blueprintSelect).val()) {
                        e.preventDefault();
                        Swal.fire('Warning', 'Please select a blueprint first.', 'warning');
                    }
                });

                // Add Blueprint Collapse & Selection
                $('#add_blueprint_details_btn').click(function() {
                    $('#add_blueprint_details_panel').collapse('toggle');
                });

                $('#add_blueprint_select').change(function() {
                    const selected = $(this).find(':selected');
                    const blueprintId = selected.val();
                    loadPrintProviders(blueprintId, '#add_provider_select');

                    if (!blueprintId) {
                        $('#add_blueprint_details_panel').collapse('hide');
                        return;
                    }
                    
                    const title = selected.text();
                    const brand = selected.data('brand');
                    const model = selected.data('model');
                    const desc = selected.data('description');
                    const images = selected.data('images') || [];
                    
                    $('#add_bp_title_disp').text(title);
                    $('#add_bp_brand_disp').text(brand || '—');
                    $('#add_bp_model_disp').text(model || '—');
                    $('#add_bp_desc_disp').html(desc || '');
                    
                    let imgHtml = '';
                    images.forEach(src => {
                        imgHtml += `<a href="${src}" target="_blank" class="me-2"><img src="${src}" class="w-80px h-80px object-fit-cover rounded border border-gray-200" alt="blueprint image"></a>`;
                    });
                    $('#add_bp_images_disp').html(imgHtml);
                    
                    $('#add_blueprint_details_panel').collapse('show');
                });

                // Add Provider Collapse & Selection
                $('#add_provider_details_btn').click(function() {
                    $('#add_provider_details_panel').collapse('toggle');
                });

                $('#add_provider_select').change(function() {
                    const selected = $(this).find(':selected');
                    if (!selected.val()) {
                        $('#add_provider_details_panel').collapse('hide');
                        return;
                    }
                    
                    const title = selected.text();
                    const address = selected.data('address');
                    const city = selected.data('city');
                    const region = selected.data('region');
                    const zip = selected.data('zip');
                    const country = selected.data('country');
                    
                    $('#add_prov_title_disp').text(title);
                    $('#add_prov_address_disp').text(address || '—');
                    $('#add_prov_city_disp').text(city || '—');
                    $('#add_prov_region_disp').text(region || '—');
                    $('#add_prov_zip_disp').text(zip || '');
                    $('#add_prov_country_disp').text(country || '—');
                    
                    $('#add_provider_details_panel').collapse('show');
                });

                // Edit Blueprint Collapse & Selection
                $('#edit_blueprint_details_btn').click(function() {
                    $('#edit_blueprint_details_panel').collapse('toggle');
                });

                $('#edit_blueprint_select').change(function(e, selectedProviderId = null) {
                    const selected = $(this).find(':selected');
                    const blueprintId = selected.val();
                    loadPrintProviders(blueprintId, '#edit_provider_select', selectedProviderId);

                    if (!blueprintId) {
                        $('#edit_blueprint_details_panel').collapse('hide');
                        return;
                    }
                    
                    const title = selected.text();
                    const brand = selected.data('brand');
                    const model = selected.data('model');
                    const desc = selected.data('description');
                    const images = selected.data('images') || [];
                    
                    $('#edit_bp_title_disp').text(title);
                    $('#edit_bp_brand_disp').text(brand || '—');
                    $('#edit_bp_model_disp').text(model || '—');
                    $('#edit_bp_desc_disp').html(desc || '');
                    
                    let imgHtml = '';
                    images.forEach(src => {
                        imgHtml += `<a href="${src}" target="_blank" class="me-2"><img src="${src}" class="w-80px h-80px object-fit-cover rounded border border-gray-200" alt="blueprint image"></a>`;
                    });
                    $('#edit_bp_images_disp').html(imgHtml);
                });

                // Edit Provider Collapse & Selection
                $('#edit_provider_details_btn').click(function() {
                    $('#edit_provider_details_panel').collapse('toggle');
                });

                $('#edit_provider_select').change(function() {
                    const selected = $(this).find(':selected');
                    if (!selected.val()) {
                        $('#edit_provider_details_panel').collapse('hide');
                        return;
                    }
                    
                    const title = selected.text();
                    const address = selected.data('address');
                    const city = selected.data('city');
                    const region = selected.data('region');
                    const zip = selected.data('zip');
                    const country = selected.data('country');
                    
                    $('#edit_prov_title_disp').text(title);
                    $('#edit_prov_address_disp').text(address || '—');
                    $('#edit_prov_city_disp').text(city || '—');
                    $('#edit_prov_region_disp').text(region || '—');
                    $('#edit_prov_zip_disp').text(zip || '');
                    $('#edit_prov_country_disp').text(country || '—');
                });

                // Edit form submit
                $('#kt_modal_edit_garment_form').on('submit', function (e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('_method', 'PUT');
                    if (!$('#edit_is_active').is(':checked')) {
                        formData.set('is_active', '0');
                    }
                    $.ajax({
                        url: $(this).attr('action'),
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        success: function (response) {
                            Swal.fire('Success!', response.message || 'Updated successfully', 'success');
                            $('#kt_modal_edit_garment').modal('hide');
                            $('#kt_table_garments').DataTable().ajax.reload();
                        },
                        error: function (xhr) {
                            let message = xhr.responseJSON?.message || 'Failed to update.';
                            if (xhr.responseJSON?.errors) {
                                const errors = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                                message = `Validation failed:<br>${errors}`;
                            }
                            Swal.fire({ title: 'Error!', html: message, icon: 'error' });
                        }
                    });
                });

                // Reset edit modal on close
                $('#kt_modal_edit_garment').on('hidden.bs.modal', function () {
                    $('#kt_modal_edit_garment_form')[0].reset();
                });

                // Delete
                $(document).on('click', '.delete-garment-btn', function (e) {
                    e.preventDefault();
                    let id = $(this).data('id');
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This action cannot be undone!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            let deleteUrl = "{{ route('garment.delete', ['id' => '___id___']) }}".replace('___id___', id);
                            $.ajax({
                                url: deleteUrl,
                                type: "DELETE",
                                data: { _token: '{{ csrf_token() }}' },
                                success: function (response) {
                                    Swal.fire('Deleted!', response.message, 'success');
                                    $('#kt_table_garments').DataTable().ajax.reload();
                                },
                                error: function (xhr) {
                                    Swal.fire('Error!', xhr.responseJSON?.message || 'Something went wrong.', 'error');
                                }
                            });
                        }
                    });
                });
            </script>
        @endpush
    </div>
@endsection
