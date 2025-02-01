@extends('Admin.template')
@section('main-section')
<style>
    .modal-dialog {
        max-width: 600px;
    }

    .custom-swal {
        display: flex;
        flex-direction: row;
        align-items: center;
    }

    .custom-swal-icon {
        display: inline-block;
        margin-right: 10px;
    }

    .custom-content {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    @media (max-width: 768px) {

        .table.data-table th:nth-child(2),
        .table.data-table td:nth-child(2) {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }
</style>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Transaction <span style="text-transform: uppercase;"><?php echo $type ?></span></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Master</li>
            <li class="breadcrumb-item active" aria-current="page">Transaction <?php echo $type ?> list</li>
        </ol>
    </div>
    <div class="btn btn-list">
        <button type="button" class="btn btn-outline-primary rounded" id="toggler" data-toggle="modal" data-target="#add_transaction_modal">
            Add Transaction
        </button>
        <div id="add_transaction_modal" class="modal fade" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Add {{ strtoupper($type) }} Transaction</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="{{ url ('submit_transaction') }}" class="ajax-form-submit" id="cform" method="post" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" id="id" value="">
                        <input type="hidden" name="type" id="type" value="<?php echo $type ?>">
                        <div class="modal-body">
                            <div class="row" style="display: flex;justify-content: center;">
                                <div class="col-12">
                                    <div class="">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label class="d-flex" for="dr_party">DR Party<span class="tx-danger">*</span></label>
                                                        <select class="form-control dr_select2" onchange="drparty(this)" name="dr_party" id="dr_party" required>
                                                            <option value=""></option>
                                                        </select>
                                                        <span class="float-left tx-danger error_text dr_party_error"></span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label class="float-left" for="dr_amount">DR Party Amount<span class="tx-danger">*</span></label>
                                                        <input type="text" class="form-control" id="dr_amount" name="dr_amount" readonly required>
                                                        <span class="float-left tx-danger error_text dr_amount_error"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label class="d-flex" for="cr_party">CR Party<span class="tx-danger">*</span></label>
                                                        <select class="form-control cr_select2" onchange="crparty(this)" name="cr_party" id="cr_party" required>
                                                            <option value=""></option>
                                                        </select>
                                                        <span class="float-left tx-danger error_text cr_party_error"></span>
                                                    </div>
                                                </div>
                                                <div class="col-lg-6">
                                                    <div class="form-group">
                                                        <label class="float-left" for="cr_amount">CR Party Amount<span class="tx-danger">*</span></label>
                                                        <input type="text" class="form-control" id="cr_amount" name="cr_amount" readonly required>
                                                        <span class="float-left tx-danger error_text cr_amount_error"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <label class="float-left" for="tras_amount">Transfer Amount<span class="tx-danger">*</span></label>
                                                        <input type="number" class="form-control" id="tras_amount" name="tras_amount" required>
                                                        <span class="float-left tx-danger error_text tras_amount_error"></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="form-group">
                                                        <label class="float-left">Notes<span class="tx-danger">*</span></label>
                                                        <textarea id="note" class="form-control" rows="4" name="note" placeholder='Enter note' required></textarea>
                                                        <span class="float-left tx-danger error_text note_error"></span>
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
                            <button class="btn btn-primary" id="save_data" type="submit" value="Submit">Submit</button>
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
                        <table class="table data-table table-striped table-hover table-fw-widget" id="table_list_data" width="100%">
                            <thead>
                                <tr>
                                    <th>Sr</th>
                                    <th style="width: 80px;">Date</th>
                                    <th></th>
                                    <th>DR Party</th>
                                    <th>CR Party</th>
                                    <th>Tr Amount</th>
                                    <th>Description</th>
                                    <th>DR Balance</th>
                                    <th>CR Balance</th>
                                    <!-- <th>Action</th> -->
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


    $('#toggler').on('click', function() {
        $(".cr_select2").val('').trigger('change');
        $(".dr_select2").val('').trigger('change');
        $('#save_data').prop('disabled', false);
        $('.form_proccessing').html('');
        document.getElementById("cform").reset();
    });

    $(document).ready(function() {
        load_data();
    });

    function load_data(filter_data = '') {
        $('.data-table').DataTable({
            columnDefs: [{
                className: 'dt-right',
                targets: [5, 7, 8]
            }, ],
            order: [
                [0, 'desc']
            ],
            iDisplayLength: 50,
            processing: true,
            serverSide: true,
            ajax: {
                data: {
                    data: filter_data,
                }
            },

            columns: [{
                    data: 'srn',
                    name: 'srn'
                },
                {
                    data: 'timest',
                    name: 'timest'
                },
                {
                    data: 'user_name',
                    name: 'user_name'
                },
                {
                    data: 'dr_party',
                    name: 'dr_party'
                },
                {
                    data: 'cr_party',
                    name: 'cr_party'
                },
                {
                    data: 'amount',
                    name: 'amount'
                },
                {
                    data: 'note',
                    name: 'note'
                },
                {
                    data: 'dr_party_balance',
                    name: 'dr_party_balance'
                },
                {
                    data: 'cr_party_balance',
                    name: 'cr_party_balance'
                },
            ]
        });
    }

    $(".dr_select2").select2({
        placeholder: "select dr party",
        width: "100%",
        ajax: {
            url: "{{ url ('getDrparty') }}",
            type: "post",
            allowClear: true,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    searchTerm: params.term,
                    cr_party: $('select[name="cr_party"]').val(),
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
    $(".cr_select2").select2({
        placeholder: "select cr party",
        width: "100%",
        ajax: {
            url: "{{ url ('getCrparty') }}",
            type: "post",
            allowClear: true,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    searchTerm: params.term,
                    dr_party: $('select[name="dr_party"]').val(),
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

    $('.ajax-form-submit').on('submit', function(e) {
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
            success: function(response) {
                if (response.st == 'success') {
                    var bookName = "{{ env('BOOK_NAME') }}";
                    var swal_html = '<div class="" style="margin-top:10px;"><div class="custom-swal" style="font-size: 22px;"><div class="custom-swal-icon"><i class="fa-regular fa-circle-check fa-shake fa-lg" style="color:green;"></i></div><div class="custom-content">{{ strtoupper($type) }} txn : ' + bookName + '</div></div></div><style>td{text-align:left;padding:8px;}.swal2-title{padding:0px}.swal2-popup{width:27em;}</style><div style="text-align:-webkit-center;font-size:15px;"><table border="1"><tr><td>Srn</td><td>:</td><td>' + response.msg.srn + '</td><td rowspan="2"></td><td>' + response.msg.date + '</td></tr><tr><td width="70px;">Dr Party</td><td>:</td><td>' + response.msg.dr_party + '</td><td style="text-align:right;">' + response.msg.amount + '</td></tr><tr><td>Notes</td><td>:</td><td colspan="4">' + response.msg.note + '</td></tr><tr><td>User</td><td>:</td><td colspan="4">' + response.msg.user + '</td></tr></table></div>';

                    swal_html += '<div style="margin-top: 20px;"></div>';

                    var second_table = '<div class="" style="margin-top:10px;"><div class="custom-swal" style="font-size: 22px;"><div class="custom-swal-icon"><i class="fa-regular fa-circle-check fa-shake fa-lg" style="color:green;"></i></div><div class="custom-content">{{ strtoupper($type) }} txn : ' + bookName + '</div></div></div><div style="text-align:-webkit-center;font-size:15px;"><table border="1"><tr><td>Srn</td><td>:</td><td>' + response.msg.srn + '</td><td rowspan="2"></td><td>' + response.msg.date + '</td></tr><tr><td width="70px;">Cr Party</td><td>:</td><td>' + response.msg.cr_party + '</td><td style="text-align:right;">' + response.msg.amount + '</td></tr><tr><td>Notes</td><td>:</td><td colspan="4">' + response.msg.note + '</td></tr><tr><td>User</td><td>:</td><td colspan="4">' + response.msg.user + '</td></tr></table></div>';

                    swal_html += second_table;

                    Swal.fire({
                        // title: '<div class="container" style="margin-top:10px;"><div class="custom-swal"><div class="custom-swal-icon"><i class="fa-regular fa-circle-check fa-shake fa-lg" style="color:green;"></i></div><div class="custom-content">{{ strtoupper($type) }} txn : ' + bookName + '</div></div></div>',
                        html: swal_html,
                        allowOutsideClick: false,
                    });
                    $('#add_transaction_modal').modal('toggle');
                    $('.data-table').DataTable().ajax.reload();
                } else if (response.st == 'failed') {
                    Swal.fire("warning!", response.msg, "error");
                } else {
                    $('.form_proccessing').html('');
                    $('#save_data').prop('disabled', false);
                    $.each(response.error, function(prefix, val) {
                        $('span.' + prefix + '_error').text(val).show().delay(5000).fadeOut();
                    });
                }
            },
            error: function() {
                $('#save_data').prop('disabled', false);
                alert('Error');
            }
        });
        return false;
    });

    function drparty(data) {
        var id = data.value;
        var type = $('#type').val();
        $.ajax({
            url: "{{ url ('getAmount') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                type: type
            },
            success: function(data) {
                $('#dr_amount').val(data);
            }
        });
    }

    function crparty(data) {
        var id = data.value;
        var type = $('#type').val();
        $.ajax({
            url: "{{ url ('getAmount') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: id,
                type: type
            },
            success: function(data) {
                $('#cr_amount').val(data);
            }
        });
    }

    function delete_entry(delete_entry) {
        var type = 'Remove';
        var srn = $(delete_entry).data('srn');
        var table_name = $(delete_entry).data('table_name');
        var info = $(delete_entry).data('info');
        var map = $(delete_entry).data('map');
        Swal.fire({
            title: 'Are you sure want to delete?',
            text: 'You can not be recover your entry.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.value) {
                $.ajax({
                    url: "{{ url ('delete_entry') }}",
                    type: 'post',
                    data: {
                        srn: srn,
                        table_name: table_name,
                        info: info,
                        map: map
                    },
                    success: function(response) {
                        if (response.st === 'success') {
                            Swal.fire('Deleted!', response.msg, 'success')
                            $('.data-table').DataTable().ajax.reload();
                        } else {
                            Swal.fire('Error', 'Can not delete this Entry.', 'error');
                        }
                    }
                });
            } else {
                swal.fire("Cancelled", "Your entry is safe", "error");

            }
        })
    }
</script>
@endsection
