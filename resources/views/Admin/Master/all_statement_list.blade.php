@extends('Admin.template')
@section('main-section')
<style>
    @media (max-width: 1600px) {

        .horizontal_table th:nth-child(2),
        .horizontal_table td:nth-child(2) {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }
</style>
<?php $types = config('global.transction_type_list'); ?>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">All Currency Statement</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Master</li>
            <li class="breadcrumb-item active" aria-current="page">Transaction History</li>
        </ol>
    </div>
    <div class="btn btn-list">
        <a class="btn btn-outline-success rounded ripple rounded" id="statementExcel"><i class="far fa-file-excel"></i> Excel</a>
        <a class="btn btn-outline-danger ripple rounded" id="all_cur_statementPdf"><i class="fas fa-file-pdf"></i> Statement</a>
        <a href="#" class="btn ripple btn-danger navresponsive-toggler" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="fe fe-filter mr-1"></i> Filter <i class="fas fa-caret-down ml-1"></i>
        </a>
    </div>
</div>
<div class="responsive-background">
    <div class="collapse navbar-collapse {{ @$cbalance == '' ? 'show' : '' }}" id="navbarSupportedContent">
        <div class="advanced-search">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <table id="myTable">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-lg-0">
                                    <div class="input-group" style="flex-wrap:nowrap;">
                                        <div class="input-group" style="flex-wrap:nowrap;">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    Select Party
                                                </div>
                                            </div>
                                            <select class="form-control partyselect2" name="party" id="party" required>
                                                <option value=""></option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
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
                            <div class="col-md-4">
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
                            <div class="col-md-12 mt-2 col-lg-4 col-sm-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="checked" name="show_party" id="show_party_checkbox">
                                    <label class="form-check-label" for="show_party_checkbox">Show Party</label>
                                </div>
                            </div>
                            <div class="col-md-12 mt-2 col-lg-3 col-sm-12">
                                <label>View</label>
                                <div class="form-group">
                                    <div class="form-group form-control">
                                        <div class="form-check form-check-inline float-left">
                                            <input class="form-check-input" type="radio" name="view" id="vertical" value="vertical" checked />
                                            <label class="form-check-label" for="vertical">Vertical</label>
                                        </div>
                                        <div class="form-check form-check-inline float-left">
                                            <input class="form-check-input" type="radio" name="view" id="horizontal" value="horizontal" />
                                            <label class="form-check-label" for="horizontal">Horizontal</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </table>
                </div>
            </div>
            <div class="text-right p-1">
                <button type="submit" class="btn btn-primary" id="filter">Apply</button>
            </div>
        </div>
    </div>
</div>

<div id="verticalContent">
    <?php foreach ($types as $type) { ?>
        <div id="info_<?php echo $type ?>" class="p-1" style="font-size:15px; font-weight: 500;">Select party to show <span style="text-transform: uppercase;"><?php echo $type ?> </span> entries</div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card custom-card">
                    <div class="card-header-divider">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table data-table transaction_<?php echo $type ?> table-striped table-hover table-fw-widget" id="transaction_<?php echo $type ?>" width="100%">
                                    <thead>
                                        <tr>
                                            <th style="width: 2%;">Sr</th>
                                            <th style="width: 25%;">Date</th>
                                            <th style="width: 1%;">User</th>
                                            <th style="width: 10%;">Party</th>
                                            <th style="width: 70%;">Description</th>
                                            <th style="width: 100px;">Debit</th>
                                            <th style="width: 100px;">Credit</th>
                                            <th style="width: 100px;">Closing Balance</th>
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
    <?php } ?>
</div>

<div id="horizontalContent" style="display: none;">
    <div id="horizontal_head" class="p-1" style="font-size:15px; font-weight: 500;">Select party to show all entries</div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card custom-card">
                <div class="card-header-divider">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table horizontal_table dataTable data-table border table-striped table-hover table-fw-widget" id="" width="100%">
                                <thead>
                                    <tr>
                                        <th rowspan="2">Sr</th>
                                        <th rowspan="2">Date</th>
                                        <th rowspan="2">User</th>
                                        <th rowspan="2">Description</th>
                                        @foreach($types as $type)
                                        <th colspan="3" class="tx-center">{{$type}}</th>
                                        @endforeach
                                    </tr>
                                    <tr>
                                        @foreach($types as $type)
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Cbalance</th>
                                        @endforeach
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
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(".partyselect2").select2({
        placeholder: "select party",
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

    $(document).ready(function() {
        $('.fc-datepicker').datepicker({
            dateFormat: 'yy-mm-dd',
            showOtherMonths: true,
            selectOtherMonths: true,
            maxDate: 0
        });

        $("input[name='view']").on("change", function() {
            if ($(this).val() === "vertical") {
                $("#verticalContent").show();
                $("#horizontalContent").hide();
            } else {
                $("#verticalContent").hide();
                $("#horizontalContent").show();
            }
        });
    });


    function load_data(party = '', fromdate = '', todate = '') {
        <?php foreach ($types as $type) { ?>
            $('.transaction_<?php echo $type ?>').DataTable({
                order: [
                    [0, 'desc']
                ],
                oLanguage: {
                    "sEmptyTable": "No entries avialable"
                },
                columnDefs: [{
                    className: 'dt-right',
                    targets: [5, 6, 7]
                }, ],
                processing: true,
                serverSide: true,
                ajax: {
                    data: {
                        party: party,
                        fromdate: fromdate,
                        todate: todate,
                        type: '<?php echo $type ?>'
                    }
                },
                columns: [{
                        data: 'srn',
                        name: 'srn'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'user_name',
                        name: 'user_name'
                    },
                    {
                        data: 'opp_party_name',
                        name: 'opp_party_name',
                        visible: setConditions()
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
                    {
                        data: 'cbalance',
                        name: 'cbalance'
                    }
                ]
            });
            $('#filter').on('submit', function(e) {
                oTable.draw();
                e.preventDefault();
            });
        <?php } ?>
    }

    function horizontal_load_data(party = '', fromdate = '', todate = '') {
        var view = 'horizontal';
        var column = [{
                data: 'srn',
                name: 'srn'
            },
            {
                data: 'date',
                name: 'date'
            },
            {
                data: 'user_name',
                name: 'user_name'
            },
            {
                data: 'note',
                name: 'note',
                render: function(data, type, full, meta) {
                    if (type === 'display') {
                        if (data.length > 12) {
                            return '<span class="tooltip-trigger" data-toggle="tooltip" data-placement="top" title="' + data + '">' + data.substr(0, 12) + '...</span>';
                        }
                    }
                    return data;
                }
            },
        ];
        var newtype = 4;
        var newtypetarget = [];
        <?php foreach ($types as $type) { ?>
            var debit = {
                data: '<?php echo $type ?>_debit',
                name: '<?php echo $type ?>_debit',
                className: 'text-right'
            };
            var credit = {
                data: '<?php echo $type ?>_credit',
                name: '<?php echo $type ?>_credit',
                className: 'text-right'
            };
            var cbalance = {
                data: '<?php echo $type ?>_cbalance',
                name: '<?php echo $type ?>_cbalance',
                className: 'text-right'
            }
            newtypetarget.push(newtype);
            newtype++;
            column.push(debit);
            column.push(credit);
            column.push(cbalance);
        <?php } ?>
        $('.horizontal_table').DataTable({
            columnDefs: [{
                className: 'dt-right',
                targets: newtypetarget
            }],
            order: [
                [1, 'desc']
            ],
            processing: true,
            serverSide: true,
            searching: false,
            pageLength: 25,
            ajax: {
                url: 'all_currency_horizontal',
                data: {
                    party: party,
                    fromdate: fromdate,
                    todate: todate,
                    all_cur: 'all',
                    view: view
                }
            },
            columns: column
        });
    }
    $('.horizontal_table').on('mouseenter', '.tooltip-trigger', function() {
        $(this).tooltip('show');
    }).on('mouseleave', '.tooltip-trigger', function() {
        $(this).tooltip('hide');
    });

    $('.horizontal_table').tooltip({
        selector: '.tooltip-trigger',
        trigger: 'manual'
    });

    function setConditions() {
        var show_party = $('input[name="show_party"]:checked').val();
        if (show_party === 'checked') {
            return true;
        } else {
            return false;
        }
    }

    $('#filter').click(function () {
        var party = $('#party').val();
        var fromdate = $('#fromdate').val();
        var todate = $('#todate').val();
        var p__name = $('#party').select2('data');

        if (party === '') {
            alert("Please select party");
            return false;
        }

        if (fromdate !== '' && todate === '') {
            alert("Please select 'to' date");
            return false;
        }

        if (fromdate === '' && todate !== '') {
            alert("Please select 'from' date");
            return false;
        }

        if (fromdate !== '' && todate !== '') {
            if (fromdate > todate) {
                alert("Please select a smaller 'from' date");
                return false;
            }
        }

        <?php foreach ($types as $type) { ?>
        $('#transaction_<?php echo $type ?>').DataTable().destroy();
        var htmlType = '<span style="text-transform: uppercase">' + '<?php echo $type ?>' + '</span>' + " " + 'Statement of' + " " + p__name[0]['text'];
        $('#info_<?php echo $type ?>').html(htmlType);
        <?php } ?>

        load_data(party, fromdate, todate);

        var htmlAllCurrency = '<span>All Currency</span>' + " " + 'Statement of' + " " + p__name[0]['text'];
        $('#horizontal_head').html(htmlAllCurrency);

        $('.horizontal_table').DataTable().destroy();

        horizontal_load_data(party, fromdate, todate);

        if (fromdate !== '' && todate !== '') {
            <?php foreach ($types as $type) { ?>
            var htmlDateRange = '<span style="text-transform: uppercase">' + '<?php echo $type ?>' + '</span>' + " " + 'Statement of' + " " + p__name[0]['text'] + " " + "from" + " " + fromdate + " " + "to" + " " + todate;
            $('#info_<?php echo $type ?>').html(htmlDateRange);
            <?php } ?>
            var horizontalDateRange = '<span>All Currency</span>' + " " + 'Statement of' + " " + p__name[0]['text'] + " " + "from" + " " + fromdate + " " + "to" + " " + todate;
            $('#horizontal_head').html(horizontalDateRange);
        }
    });


    $('#all_cur_statementPdf').click(function() {
        var party = $('#party').val();
        if (party === '') {
            alert('Please select party');
            return false;
        }
        var fromdate = $('#fromdate').val();
        var todate = $('#todate').val();
        var p__name = $('#party').select2('data');
        var show_party = $('[name=show_party]:checked').val();
        var view = $('[name=view]:checked').val();

        $.ajax({
            url: 'all_cur_statement_pdf',
            cache: false,
            data: {
                party: party,
                fromdate: fromdate,
                todate: todate,
                party_name: p__name[0]['text'],
                show_party: show_party,
                view: view
            },

            xhr: function() {
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 2) {
                        if (xhr.status == 200) {
                            xhr.responseType = "blob";
                        } else {
                            xhr.responseType = "text";
                        }
                    }
                };
                return xhr;
            },
            success: function(data) {
                var blob = new Blob([data], {
                    type: "application/octetstream"
                });
                var isIE = false || !!document.documentMode;
                if (isIE) {
                    window.navigator.msSaveBlob(blob, fileName);
                } else {
                    var url = window.URL || window.webkitURL;
                    link = url.createObjectURL(blob);
                    var a = $("<a />");
                    a.attr("download", p__name[0]['text'] + '_Statements.pdf');
                    a.attr("href", link);
                    $("body").append(a);
                    a[0].click();
                    $("body").remove(a);
                }
            }
        });
    });

    $('#statementExcel').click(function() {
        var party = $('#party').val();
        if (party == '') {
            alert('Please select party');
            return false;
        }
        var fromdate = $('#fromdate').val();
        var todate = $('#todate').val();
        var all_cur = 'all';
        var p__name = $('#party').select2('data');
        var show_party = $('[name=show_party]:checked').val();
        var view = $('[name=view]:checked').val();
        $.ajax({
            url: 'statement_excel',
            cache: false,
            data: {
                party: party,
                fromdate: fromdate,
                todate: todate,
                all_cur: all_cur,
                party_name: p__name[0]['text'],
                show_party: show_party,
                view:view
            },
            xhrFields: {
                responseType: 'blob' // Set the response type to 'blob'
            },
            success: function(data) {
                var a = document.createElement('a');
                var url = window.URL.createObjectURL(data);
                a.href = url;
                a.download = p__name[0]['text'] + '_All_curr_Statement.xlsx';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            }
        });
    });
</script>
@endsection
