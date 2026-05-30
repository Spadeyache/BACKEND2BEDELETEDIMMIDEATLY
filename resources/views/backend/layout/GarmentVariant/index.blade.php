@extends('backend.app')

@section('title', 'List of Garment Variants')

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
                    <h1 class="text-dark fw-bold my-1 fs-2">List of Garment Variants</h1>
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home</a>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('garment.index') }}" class="text-muted text-hover-primary">Garments</a>
                        </li>
                        <li class="breadcrumb-item text-dark">Variants</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            @include('backend.partials.garment_variant.index_main')
        </div>

        @push('script')
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

            <script>
                $('input[data-kt-variant-table-filter="search"]').on('keyup', function () {
                    $('#kt_table_variants').DataTable().search(this.value).draw();
                });

                $(document).ready(function () {
                    $('#kt_table_variants').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('garment-variant.index') }}",
                            type: "get",
                        },
                        columns: [
                            { data: 'garment_name',     name: 'garment_name', orderable: false },
                            { data: 'size',             name: 'size' },
                            { data: 'color',            name: 'color', orderable: false, searchable: false },
                            { data: 'blank_mockup_url', name: 'blank_mockup_url', orderable: false, searchable: false },
                            { data: 'price_cents',      name: 'price_cents' },
                            { data: 'is_enabled',       name: 'is_enabled', orderable: false, searchable: false },
                            { data: 'display_order',    name: 'display_order' },
                            { data: 'actions',          name: 'actions', orderable: false, searchable: false },
                        ],
                        drawCallback: function () {
                            if (typeof KTMenu !== 'undefined') KTMenu.createInstances();
                        }
                    });
                });

                function loadPrintifyVariants(garmentId, targetSelect, selectedVariantId = null) {
                    if (!garmentId) {
                        $(targetSelect).html('<option value="">— Select Variant —</option>');
                        return;
                    }
                    const url = "{{ route('garment-variant.printify-variants', ['id' => '__id__']) }}".replace('__id__', garmentId);
                    $(targetSelect).html('<option value="">Loading...</option>');
                    $.getJSON(url, function (resp) {
                        let options = '<option value="">— Select Variant —</option>';
                        if (resp.status === 'success' && resp.variants) {
                            resp.variants.forEach(v => {
                                const selected = selectedVariantId == v.id ? 'selected' : '';
                                options += `<option value="${v.id}" ${selected}>${v.title} (ID: ${v.id})</option>`;
                            });
                        }
                        $(targetSelect).html(options);
                        if ($(targetSelect).hasClass('select2-hidden-accessible')) {
                            $(targetSelect).trigger('change');
                        } else {
                            $(targetSelect).select2({ dropdownParent: $(targetSelect).closest('.modal') });
                        }
                    }).fail(function() {
                        $(targetSelect).html('<option value="">— Select Variant —</option>');
                    });
                }

                $('#add_garment_id').on('change', function () {
                    const garmentId = $(this).val();
                    loadPrintifyVariants(garmentId, '#add_printify_variant_id');
                });

                $('#edit_garment_id').on('change', function () {
                    const garmentId = $(this).val();
                    loadPrintifyVariants(garmentId, '#edit_printify_variant_id');
                });

                // Edit modal open
                $(document).on('click', '.edit-variant-btn', function (e) {
                    e.preventDefault();
                    const id = $(this).data('id');
                    $.ajax({
                        url: "{{ route('garment-variant.edit', ['id' => '__id__']) }}".replace('__id__', id),
                        type: "GET",
                        success: function (response) {
                            const v = response.variant;
                            $('#kt_modal_edit_variant_form').attr('action', "{{ route('garment-variant.update', ['id' => '__id__']) }}".replace('__id__', id));

                            $('#edit_garment_id').val(v.garment_id);
                            
                            // Load printify variants for the selected garment and select the existing one
                            loadPrintifyVariants(v.garment_id, '#edit_printify_variant_id', v.printify_variant_id);

                            $('#edit_size').val(v.size);
                            $('#edit_color').val(v.color);
                            $('#edit_color_hex').val(v.color_hex);
                            $('#edit_price_cents').val(v.price_cents);
                            $('#edit_display_order').val(v.display_order);
                            $('#edit_is_enabled').prop('checked', v.is_enabled == 1);

                            const previewUrl = v.blank_mockup_url_preview || "{{ asset('backend/assets/media/svg/files/blank-image.svg') }}";
                            $('#edit_blank_mockup_preview').css('background-image', 'url(' + previewUrl + ')');

                            $('#kt_modal_edit_variant').modal('show');
                        },
                        error: function (xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to fetch data.', 'error');
                        }
                    });
                });

                // Edit form submit
                $('#kt_modal_edit_variant_form').on('submit', function (e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('_method', 'PUT');
                    if (!$('#edit_is_enabled').is(':checked')) {
                        formData.set('is_enabled', '0');
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
                            $('#kt_modal_edit_variant').modal('hide');
                            $('#kt_table_variants').DataTable().ajax.reload();
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
                $('#kt_modal_edit_variant').on('hidden.bs.modal', function () {
                    $('#kt_modal_edit_variant_form')[0].reset();
                    $('#edit_blank_mockup_preview').css('background-image', '');
                });

                // Delete
                $(document).on('click', '.delete-variant-btn', function (e) {
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
                            let deleteUrl = "{{ route('garment-variant.delete', ['id' => '___id___']) }}".replace('___id___', id);
                            $.ajax({
                                url: deleteUrl,
                                type: "DELETE",
                                data: { _token: '{{ csrf_token() }}' },
                                success: function (response) {
                                    Swal.fire('Deleted!', response.message, 'success');
                                    $('#kt_table_variants').DataTable().ajax.reload();
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
