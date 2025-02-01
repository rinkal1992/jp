@extends('Admin.template')
@section('main-section')
    <div class="page-header">
        <div>
            <h2 class="main-content-title tx-24 mg-b-5">Party</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Master</li>
                <li class="breadcrumb-item active" aria-current="page">Party List</li>
            </ol>
        </div>
        <div class="btn btn-list">
            <button type="button" class="btn btn-outline-primary rounded" id="group_toggler" data-toggle="modal"
                    data-target="#add_group_modal">
                Add Group
            </button>
            <div id="add_group_modal" class="modal fade" role="dialog" aria-labelledby="exampleModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Add Group</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ url ('add_group') }}" class="ajax-form-submit" id="group_form" method="post"
                              enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" id="id" value="">
                            <div class="modal-body">
                                <div class="row" style="display: flex;justify-content: center;">
                                    <div class="col-12">
                                        <div class="">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            <label class="float-left" for="group_name">Group Name<span
                                                                    class="tx-danger">*</span></label>
                                                            <input type="text" class="form-control" id="group_name"
                                                                   name="group_name" placeholder="Enter group name"
                                                                   required>
                                                            <span
                                                                class="float-left tx-danger error_text group_name_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="error-msg tx-danger"></div>
                                        <div class="form_proccessing tx-success float-left"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-primary" id="save_data" type="submit" value="Submit">Submit
                                </button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-outline-primary rounded" id="toggler" data-toggle="modal"
                    data-target="#add_party_modal">
                Add Party
            </button>
            <div id="add_party_modal" class="modal fade" role="dialog" aria-labelledby="exampleModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Add Party</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ url ('add_party') }}" class="ajax-form-submit" id="cform" method="post"
                              enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" id="id" value="">
                            <div class="modal-body">
                                <div class="row" style="display: flex;justify-content: center;">
                                    <div class="col-12">
                                        <div class="">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            <label class="float-left" for="party_name">Party Name<span
                                                                    class="tx-danger">*</span></label>
                                                            <input type="text" class="form-control" id="party_name"
                                                                   name="party_name" placeholder="Enter party name"
                                                                   required>
                                                            <span
                                                                class="float-left tx-danger error_text party_name_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            <label class="float-left" for="party_details">Party
                                                                Details<span class="tx-danger">*</span></label>
                                                            <input type="text" class="form-control" id="party_details"
                                                                   name="party_details" placeholder="Enter details"
                                                                   required>
                                                            <span
                                                                class="float-left tx-danger error_text party_details_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="form-group">
                                                            <label class="d-flex" for="group_id">Select Group</label>
                                                            <select class="form-control select2" name="group_id" id="group_id">
                                                                <option value=""></option>
                                                            </select>
                                                            <span
                                                                class="float-left tx-danger error_text dr_party_error"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="error-msg tx-danger"></div>
                                        <div class="form_proccessing tx-success float-left"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-primary" id="save_data" type="submit" value="Submit">Submit
                                </button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card custom-card">
                <div class="card-header-divider">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table data-table table-striped table-hover table-fw-widget"
                                   id="table_list_data" width="100%">
                                <thead>
                                <tr>
                                    <th>Srn</th>
                                    <th>User</th>
                                    <th>Party Name</th>
                                    <th>AC Number</th>
                                    <th>Details</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#toggler').on('click', function () {
            $('#id').val('');
            document.getElementById("cform").reset();
            $('.modal-title').html('Add Party');
        });

        $('#group_toggler').on('click', function () {
            $('#id').val('');
            document.getElementById("group_form").reset();
            $('.modal-title').html('Add Group');
        });

        $(document).ready(function () {
            load_data();
        });

        function load_data(filter_data = '') {
            $('.data-table').DataTable({
                iDisplayLength: 25,
                processing: true,
                serverSide: true,
                ajax: {
                    data: {
                        data: filter_data,
                    }
                },
                order: [
                    [0, 'desc']
                ],
                columns: [{
                    data: 'srn',
                    name: 'srn'
                },
                    {
                        data: 'user_name',
                        name: 'user_name'
                    },
                    {
                        data: 'party_name',
                        name: 'party_name'
                    },
                    {
                        data: 'ac_number',
                        name: 'ac_number'
                    },
                    {
                        data: 'details',
                        name: 'details'
                    },
                ]
            });
        }

        $('.ajax-form-submit').on('submit', function (e) {
            $('#save_data').prop('disabled', true);
            $('.error-msg').html('');
            $('.form_proccessing').html('Please wait...');
            e.preventDefault();
            var aurl = $(this).attr('action');
            var form = $(this);
            var formdata = false;
            if (window.FormData) {
                formdata = new FormData(form[0]);
            }
            $.ajax({
                type: "POST",
                url: aurl,
                cache: false,
                contentType: false,
                processData: false,
                data: formdata ? formdata : form.serialize(),
                success: function (response) {
                    if (response.st === 'success') {
                        $('#add_party_modal').modal('hide');
                        $('#add_group_modal').modal('hide');
                        $('.form_proccessing').html('');
                        $('#save_data').prop('disabled', false);
                        Swal.fire("Success!", response.msg, "success");
                        $('.data-table').DataTable().ajax.reload();
                    } else {
                        $('.form_proccessing').html('');
                        $('#save_data').prop('disabled', false);
                        $.each(response.error, function (prefix, val) {
                            $('span.' + prefix + '_error').text(val).show().delay(5000).fadeOut();
                        });
                    }
                },
                error: function () {
                    $('#save_data').prop('disabled', false);
                    alert('Error');
                }
            });
            return false;
        });


        $(".select2").select2({
            placeholder: "Default",
            width: "100%",
            ajax: {
                url: "{{ url ('getGroups') }}",
                type: "post",
                allowClear: true,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        searchTerm: params.term,
                    };
                },
                processResults: function(response) {
                    return {
                        results: response
                    };
                },
                cache: true
            }
        });
    </script>
@endsection
