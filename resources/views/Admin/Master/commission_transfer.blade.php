@extends('Admin.template')
@section('main-section')
<?php $types = config('global.transction_type_list'); ?>
<style>
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
</style>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Commission Transfer</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Master</li>
            <li class="breadcrumb-item active" aria-current="page">Commission Transfer list</li>
        </ol>
    </div>
</div>
<form action="{{ url('submit_transaction') }}" class="ajax-form-submit" id="cform" method="post" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="trans_type" id="trans_type" value="commission">
    <div class="row" style="display: flex;justify-content: center;">
        <div class="col-lg-9 col-12">
            <div class="card custom-card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="float-left" for="cr_amount">Select Currency<span class="tx-danger">*</span></label>
                                <select class="form-control type" id="type" name="type" style="text-transform: uppercase;">
                                    @if($types)
                                    @foreach($types as $type)
                                    <option value="{{$type}}">{{$type}}</option>
                                    @endforeach
                                    @endif
                                </select>
                                <div class="pt-2">
                                    <label class="d-flex" for="dr_party">DR Party<span class="tx-danger">*</span></label>
                                    <select class="form-control dr_select2" onchange="drparty(this)" name="dr_party" id="dr_party" required>
                                        <option value=""></option>
                                    </select>
                                </div>
                                <span class="float-left tx-danger error_text dr_party_error"></span>
                                <div class="pt-2">
                                    <label class="float-left" for="dr_amount">DR Party Amount<span class="tx-danger">*</span></label>
                                    <input type="text" class="form-control" id="dr_amount" name="dr_amount" readonly>
                                    <span class="float-left tx-danger error_text dr_amount_error"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="float-left" for="tras_amount">Transfer Amount<span class="tx-danger">*</span></label>
                                <input type="number" class="form-control" id="tras_amount" name="tras_amount" required>
                                <span class="float-left tx-danger error_text tras_amount_error"></span>
                                <div class="pt-2">
                                    <label class="d-flex" for="cr_party">CR Party<span class="tx-danger">*</span></label>
                                    <select class="form-control cr_select2" onchange="crparty(this)" name="cr_party" id="cr_party" required>
                                        <option value=""></option>
                                    </select>
                                    <span class="float-left tx-danger error_text cr_party_error"></span>
                                </div>
                                <div class="pt-2">
                                    <label class="float-left" for="cr_amount">CR Party Amount<span class="tx-danger">*</span></label>
                                    <input type="text" class="form-control" id="cr_amount" name="cr_amount" readonly>
                                    <span class="float-left tx-danger error_text cr_amount_error"></span>
                                </div>
                            </div>
                        </div>
                        <style>
                            .center {
                                margin: auto;
                            }
                        </style>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="float-left">Notes<span class="tx-danger">*</span></label>
                                <textarea id="note" class="form-control" rows="4" name="note" placeholder='Enter note' value="" style="height: 38px;"><?= date('d-m-Y') ?></textarea>
                                <span class=" float-left tx-danger error_text note_error"></span>
                            </div>
                            <div class="center text-center pt-3">
                                <button class="btn btn-primary" id="save_data" type="submit" value="Submit">Submit</button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <div class="error-msg tx-danger"></div>
                            <div class="form_proccessing text-center tx-success"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<div class="row">
    <div class="col-md-3">
        <div class="form-group mb-lg-0">
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        Select Currency
                    </div>
                </div>
                <select class="form-control" id="filter_type" name="filter_type" style="text-transform: uppercase;">
                    @if($types)
                    @foreach($types as $type)
                    <option value="{{$type}}">{{$type}}</option>
                    @endforeach
                    @endif
                </select>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-lg-0">
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        From Date:
                    </div>
                </div>
                <input class="form-control fc-datepicker fromdate" id="fromdate" name="fromdate" placeholder="YYYY-MM-DD" type="text" autocomplete="off">
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-lg-0">
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">
                        To Date:
                    </div>
                </div>
                <input class="form-control fc-datepicker todate" id="todate" name="todate" placeholder="YYYY-MM-DD" type="text" autocomplete="off">
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group mb-lg-0">
            <div class="input-group">
                <button type="submit" class="btn btn-primary" id="filter">Apply</button>
                <button type="reset" class="btn btn-danger ml-2" id="reset">Reset</button>
            </div>
            <!-- <div class="input-group">
            </div> -->
        </div>
    </div>
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header-divider">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table data-table table-striped table-hover table-fw-widget" id="table_list_data" width="100%">
                            <thead>
                                <tr>
                                    <th style="width: 2%;">Sr</th>
                                    <th style="width: 10%;">Dr Party</th>
                                    <th style="width: 10%;">Cr Party</th>
                                    <th style="width: 13%;">Tr Amount</th>
                                    <th>Note</th>
                                    <th style="width: 13%;">Dr Balance</th>
                                    <th style="width: 13%;">Cr Balance</th>
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

    $(document).ready(function() {
        load_data();
        $('.fc-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            showOtherMonths: true,
            selectOtherMonths: true,
            maxDate: 0
        });
        $('#type').on('change', function() {
            $('#dr_party').val('').trigger('change');
            $('#dr_amount').val('');

            $('#cr_party').val('').trigger('change');
            $('#cr_amount').val('');
        });
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
                console.log(response);
                if (response.st == 'success') {
                    var bookName = "{{ env('BOOK_NAME') }}";
                    var swal_html = '<style>td{text-align:left;padding:8px;}.swal2-title{padding:0px}.swal2-popup{width:27em;}</style><div style=text-align:-webkit-center;font-size:15px;><table border="1"><tr><td>Srn</td><td>:-</td><td>' + response.msg.srn + '</td></tr><tr><td>Date</td><td>:-</td><td>' + response.msg.date + '</td></tr><tr><td>Dr Party</td><td>:-</td><td>' + response.msg.dr_party + '</td></tr><tr><td>Cr Party</td><td>:-</td><td>' + response.msg.cr_party + '</td></tr><tr><td>Amount</td><td>:-</td><td>' + response.msg.amount + '</td></tr><tr><td>Notes</td><td>:-</td><td>' + response.msg.note + '</td></tr><tr><td>User</td><td>:-</td><td>' + response.msg.user + '</td></tr></table></div>'
                    Swal.fire({
                        title: '<div class="container" style="margin-top:10px;"><div class="custom-swal"><div class="custom-swal-icon"><i class="fa-regular fa-circle-check fa-shake fa-lg" style="color:green;"></i></div><div class="custom-content" style="font-size: 23px;">' + response.msg.type.toUpperCase() + ' comm : ' + bookName + ' </div></div></div>',
                        html: swal_html,
                        allowOutsideClick: false,
                    }).then(() => {
                        location.reload()
                    });
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

    function load_data(filter_type = 'inr', fromdate = '', todate = '') {
        $('.data-table').DataTable({
            order: [
                [0, 'desc']
            ],
            columnDefs: [{
                className: 'dt-right',
                targets: [3, 5, 6]
            }, ],
            processing: true,
            serverSide: true,
            ajax: {
                data: {
                    fromdate: fromdate,
                    todate: todate,
                    type: filter_type,
                }
            },
            columns: [{
                    data: 'srn',
                    name: 'srn'
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

    $('#filter').click(function() {
        var fromdate = $('#fromdate').val();
        var todate = $('#todate').val();
        var filter_type = $('#filter_type').val();
        $('#table_list_data').DataTable().destroy();
        load_data(filter_type, fromdate, todate);
    });

    $('#reset').click(function() {
        var fromdate = $('#fromdate').val('');
        var todate = $('#todate').val('');
        $('#table_list_data').DataTable().destroy();
        load_data();
    })

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
                    type: 'with_exch',
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
</script>
@endsection
