@extends('backend.app')

@section('title', ' List of Veara Products')

@push('style')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        .dataTables_filter {
            display: none !important;
        }
    </style>
@endpush

@section('content')
    <div class="content fs-6 d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="toolbar" id="kt_toolbar">
            <div class=" container-fluid  d-flex flex-stack flex-wrap flex-sm-nowrap">
                <div class="d-flex flex-column align-items-start justify-content-center flex-wrap me-2">
                    <h1 class="text-dark fw-bold my-1 fs-2">List of Veara Products</h1>
                    <ul class="breadcrumb fw-semibold fs-base my-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('dashboard') }}" class="text-muted text-hover-primary">Home </a>
                        </li>
                        <li class="breadcrumb-item text-dark">Veara Products</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="post fs-6 d-flex flex-column-fluid" id="kt_post">
            @include('backend.partials.veara_product.index_main')
        </div>

        @push('script')
            <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
            <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

            <script>
                $('input[data-kt-product-table-filter="search"]').on('keyup', function() {
                    $('#kt_table_products').DataTable().search(this.value).draw();
                });

                $(document).ready(function() {
                    $('#kt_table_products').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: "{{ route('veara-product.index') }}",
                            type: "get",
                        },
                        columns: [
                            { data: 'veara_front', name: 'veara_front', orderable: false, searchable: false },
                            { data: 'product_id', name: 'product_id' },
                            { data: 'design_type', name: 'design_type' },
                            { data: 'category', name: 'category' },
                            { data: 'created_at', name: 'created_at' },
                            { data: 'actions', name: 'actions', orderable: false, searchable: false }
                        ],
                        drawCallback: function() {
                            if (typeof KTMenu !== 'undefined') {
                                KTMenu.createInstances();
                            }
                        }
                    });
                });

                // edit modal show
                $(document).on('click', '.edit-product-btn', function(e) {
                    e.preventDefault();
                    const id = $(this).data('id');
                    $.ajax({
                        url: "{{ route('veara-product.edit', ['id' => '__id__']) }}".replace('__id__', id),
                        type: "GET",
                        success: function(response) {
                            $('#kt_modal_edit_product_form').attr('action', "{{ route('veara-product.update', ['id' => '__id__']) }}".replace('__id__', id));
                            
                            $('#edit_product_id').val(response.product.product_id);
                            $('#edit_title').val(response.product.title);
                            $('#edit_price').val(response.product.price);
                            $('#edit_description').val(response.product.description);
                            $('#edit_style_tags').val(response.product.style_tags);
                            
                            // Parse color_palette if it's string to show in input
                            let colorPalette = response.product.color_palette;
                            if (typeof colorPalette === 'object' && colorPalette !== null) {
                                $('#edit_color_palette').val(JSON.stringify(colorPalette));
                            } else {
                                $('#edit_color_palette').val(colorPalette);
                            }

                            $('#edit_design_type').val(response.product.design_type);
                            $('#edit_category').val(response.product.category);
                            $('#edit_subject_matter').val(response.product.subject_matter);
                            $('#edit_mood').val(response.product.mood);
                            $('#edit_complexity_score').val(response.product.complexity_score);
                            $('#edit_pet_relevance_score').val(response.product.pet_relevance_score);
                            $('#edit_target_audience_guess').val(response.product.target_audience_guess);
                            $('#edit_seasonal_fit').val(response.product.seasonal_fit);
                            
                            $('#edit_veara_front_preview').css('background-image', 'url(' + (response.product.veara_front ? response.product.veara_front : "{{ asset('backend/assets/media/svg/files/blank-image.svg') }}") + ')');
                            $('#edit_veara_back_preview').css('background-image', 'url(' + (response.product.veara_back ? response.product.veara_back : "{{ asset('backend/assets/media/svg/files/blank-image.svg') }}") + ')');
                            $('#edit_front_mockup_preview').css('background-image', 'url(' + (response.product.front_mockup ? response.product.front_mockup : "{{ asset('backend/assets/media/svg/files/blank-image.svg') }}") + ')');
                            $('#edit_back_mockup_preview').css('background-image', 'url(' + (response.product.back_mockup ? response.product.back_mockup : "{{ asset('backend/assets/media/svg/files/blank-image.svg') }}") + ')');
                            
                            $('#kt_modal_edit_product').modal('show');
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to fetch data.', 'error');
                        }
                    });
                });

                // Handle edit form submission
                $('#kt_modal_edit_product_form').on('submit', function(e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('_method', 'PUT'); 
                    $.ajax({
                        url: $(this).attr('action'),
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        success: function(response) {
                            Swal.fire('Success!', response.message || 'Updated successfully', 'success');
                            $('#kt_modal_edit_product').modal('hide');
                            $('#kt_table_products').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
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
                $('#kt_modal_edit_product').on('hidden.bs.modal', function() {
                    $('#kt_modal_edit_product_form')[0].reset();
                });

                // delete
                $(document).on('click', '.delete-product-btn', function(e) {
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
                            let deleteUrl = "{{ route('veara-product.delete', ['id' => '___id___']) }}".replace('___id___', id);
                            $.ajax({
                                url: deleteUrl,
                                type: "DELETE",
                                data: { _token: '{{ csrf_token() }}' },
                                success: function(response) {
                                    Swal.fire('Deleted!', response.message, 'success');
                                    $('#kt_table_products').DataTable().ajax.reload();
                                },
                                error: function(xhr) {
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
