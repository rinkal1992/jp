@extends('Admin.template')
@section('main-section')
<?php $types = config('global.transction_type_list'); ?>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Statement</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Master</li>
            <li class="breadcrumb-item active" aria-current="page">Transaction History</li>
        </ol>
    </div>
    <div class="btn btn-list">
        <a class="btn btn-outline-success rounded ripple rounded" id="statementExcel"><i class="far fa-file-excel"></i> Excel</a>
        <a class="btn btn-outline-danger ripple rounded" id="statementPdf"><i class="fas fa-file-pdf"></i> Statement</a>
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
                            <div class="col-md-3">
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
                                    <div class="input-group" style="flex-wrap:nowrap;">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text">
                                                Select Type
                                            </div>
                                        </div>
                                        <select class="form-control type" id="type" name="type" style="text-transform: uppercase;">
                                            <option value="" disabled>select type</option>
                                            <?php foreach ($types as $type) { ?>
                                                <option value="<?php echo $type ?>"><?php echo $type ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mt-2 col-lg-3 col-sm-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="checked" name="show_party" id="show_party_checkbox">
                                    <label class="form-check-label" for="show_party_checkbox">Show Party</label>
                                </div>
                            </div>
                        </div>
                        <div class="text-right p-1">
                            <button type="submit" class="btn btn-primary" id="filter">Apply</button>
                        </div>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="info" class="p-1" style="font-size:15px; font-weight: 500;">Select party to show entries</div>
<div class="row">
    <div class="col-lg-12">
        <div class="card custom-card">
            <div class="card-header-divider">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table data-table table-striped table-hover table-fw-widget" id="table_list_data" width="100%">
                            <thead>
                                <tr>
                                    <th style="width: 2%;">Sr</th>
                                    <th style="width: 15%;">Date</th>
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
            maxDate: 0,
        });
    });

    function load_data(party = '', fromdate = '', todate = '', type = '') {
        $('.data-table').DataTable({
            order: [
                [0, 'desc']
            ],
            oLanguage: {
                "sEmptyTable": "No entries avialable"
            },
            columnDefs: [{
                className: 'dt-right',
                targets: [5, 6, 7]
            }],
            processing: true,
            serverSide: true,
            ajax: {
                data: {
                    party: party,
                    fromdate: fromdate,
                    todate: todate,
                    type: type,
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
    }

    function setConditions() {
        var show_party = $('input[name="show_party"]:checked').val();
        if (show_party == 'checked') {
            return true;
        } else {
            return false;
        }
    }

    $('#filter').click(function () {
        var party = $('#party').val();
        var type = $('#type').val();
        var fromdate = $('#fromdate').val();
        var todate = $('#todate').val();
        var p__name = $('#party').select2('data');

        if (party === '') {
            alert("Please select party");
            return false;
        }

        if (fromdate !== '' && todate !== '') {
            if (fromdate > todate) {
                alert("Please select a smaller 'from' date");
                return false;
            }

            var dateRangeInfo = ' from ' + fromdate + ' to ' + todate;
        } else if (fromdate !== '' && todate === '') {
            alert("Please select 'to' date");
            return false;
        } else if (fromdate === '' && todate !== '') {
            alert("Please select 'from' date");
            return false;
        }

        $('#table_list_data').DataTable().destroy();

        var infoText = '<span style="text-transform: uppercase">' + type + '</span> Statement of ' + p__name[0]['text'];
        if (dateRangeInfo) {
            infoText += dateRangeInfo;
        }

        $('#info').html(infoText);

        load_data(party, fromdate, todate, type);
    });

    $('#statementPdf').click(function() {
        var party = $('#party').val();
        if (party == '') {
            alert('Please select party');
            return false;
        }
        var fromdate = $('#fromdate').val();
        var todate = $('#todate').val();
        var type = $('#type').val();
        var p__name = $('#party').select2('data');
        var show_party = $('[name=show_party]:checked').val();

        $.ajax({
            url: 'statement_pdf',
            cache: false,
            data: {
                party: party,
                fromdate: fromdate,
                todate: todate,
                type: type,
                party_name: p__name[0]['text'],
                show_party: show_party
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
                //Convert the Byte Data to BLOB object.
                var blob = new Blob([data], {
                    type: "application/octetstream"
                });
                //Check the Browser type and download the File.
                var isIE = false || !!document.documentMode;
                if (isIE) {
                    window.navigator.msSaveBlob(blob, fileName);
                } else {
                    var url = window.URL || window.webkitURL;
                    link = url.createObjectURL(blob);
                    var a = $("<a />");
                    a.attr("download", p__name[0]['text'] + '_Statement.pdf');
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
        var type = $('#type').val();
        var p__name = $('#party').select2('data');
        var show_party = $('[name=show_party]:checked').val();

        $.ajax({
            url: 'statement_excel',
            cache: false,
            data: {
                party: party,
                fromdate: fromdate,
                todate: todate,
                type: type,
                party_name: p__name[0]['text'],
                show_party: show_party
            },
            xhrFields: {
                responseType: 'blob' // Set the response type to 'blob'
            },
            success: function(data) {
                var a = document.createElement('a');
                var url = window.URL.createObjectURL(data);
                a.href = url;
                a.download = p__name[0]['text'] + '_Statement.xlsx';
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            }
        });
    });
</script>
@endsection
