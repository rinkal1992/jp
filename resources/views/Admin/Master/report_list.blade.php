@extends('Admin.template')
@section('main-section')
<?php $types = config('global.transction_type_list'); ?>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Report</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Master</li>
            <li class="breadcrumb-item active" aria-current="page">Transaction History</li>
        </ol>
    </div>
    <div class="btn btn-list">
        <a href="#" class="btn ripple btn-danger navresponsive-toggler" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fe fe-filter mr-1"></i> Filter <i class="fas fa-caret-down ml-1"></i>
        </a>
    </div>
</div>
<div class="responsive-background">
    <div class="collapse navbar-collapse show" id="navbarSupportedContent">
        <div class="advanced-search">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <table id="myTable">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-lg-0">
                                    <div class="input-group" style="flex-wrap:nowrap;">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                Currency
                                            </div>
                                        </div>
                                        <select class="form-control type" id="type" name="type" style="text-transform: uppercase;">
                                            <option value="" disabled>select currency</option>
                                            <?php foreach ($types as $type) { ?>
                                                <option value="<?php echo $type ?>"><?php echo $type ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-lg-0">
                                    <div class="input-group" style="flex-wrap:nowrap;">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                DR Party
                                            </div>
                                        </div>
                                        <select class="form-control dr_select2" name="dr_party" id="dr_party" required>
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-lg-0">
                                    <div class="input-group" style="flex-wrap:nowrap;">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                CR Party
                                            </div>
                                        </div>
                                        <select class="form-control cr_select2" name="cr_party" id="cr_party" required>
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-lg-0">
                                    <div class="input-group" style="flex-wrap:nowrap;">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                User
                                            </div>
                                        </div>
                                        <select class="form-control userselect2" name="user" id="user" required>
                                            <option value=""></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </table>
                </div>
            </div>
            <div class="text-right p-3">
                <button type="submit" class="btn btn-primary" id="filter">Apply</button>
                <a href="#" id="refresh" class="btn btn-danger" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">Reset</a>
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
                                    <th></th>
                                    <th>DR Party</th>
                                    <th>CR Party</th>
                                    <th>Tr Amount</th>
                                    <th>Description</th>
                                    <th>DR Balance</th>
                                    <th>CR Balance</th>
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
    });

    function load_data(type = 'inr', dr_party = '', cr_party = '', user = '') {
        $('.data-table').DataTable({
            columnDefs: [{
                className: 'dt-right',
                targets: [4, 6, 7]
            }, ],
            order: [
                [0, 'desc']
            ],
            processing: true,
            serverSide: true,
            ajax: {
                data: {
                    type: type,
                    dr_party: dr_party,
                    cr_party: cr_party,
                    user: user
                }
            },
            columns: [{
                    data: 'srn',
                    name: 'srn'
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
        $('#filter').on('submit', function(e) {
            oTable.draw();
            e.preventDefault();
        });
    }

    $('#filter').click(function() {
        var type = $('#type').val();
        var dr_party = $('#dr_party').val();
        var cr_party = $('#cr_party').val();
        var user = $('#user').val();
        if (type != '') {
            $('#table_list_data').DataTable().destroy();
            load_data(type, dr_party, cr_party, user);
        }
    });

    $('#refresh').click(function() {
        $(".cr_select2").val('').trigger('change');
        $(".dr_select2").val('').trigger('change');
        $(".userselect2").val('').trigger('change');
        $(".type").val('inr').trigger('change');
        $('#table_list_data').DataTable().destroy();
        load_data();
    });

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
                    type: 'with_exch',
                    searchTerm: params.term, // search term
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
                    type: 'with_exch',
                    searchTerm: params.term, // search term
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
    $(".userselect2").select2({
        placeholder: "select user",
        width: "100%",
        ajax: {
            url: "{{ url ('getUser') }}",
            type: "post",
            allowClear: true,
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    searchTerm: params.term, // search term
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
