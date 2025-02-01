@extends('Admin.template')
@section('main-section')
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

        @media (max-width: 768px) {

            .table.data-table th:nth-child(2),
            .table.data-table td:nth-child(2) {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }
    </style>
    <?php $types = config('global.transction_type_list'); ?>
    <div class="page-header">
        <div>
            @php
                $titleParts = explode('_', @$title);
            @endphp
            <h2 class="main-content-title tx-24 mg-b-5">{{ ucfirst($titleParts[0]) }} {{strtoupper($titleParts[1])}}</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Master</li>
                <li class="breadcrumb-item active" aria-current="page">{{ ucfirst($titleParts[0]) }} List</li>
            </ol>
        </div>
    </div>
    <div class="responsive-background">
        <div class="collapse navbar-collapse show" id="navbarSupportedContent">
            <div class="advanced-search">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="d-flex" for="party">Select Party<span
                                                class="tx-danger">*</span></label>
                                    <select class="form-control partyselect2" name="party" id="party">
                                        <option value=""></option>
                                    </select>
                                    <span class="float-left tx-danger error_text party_error"></span>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <?php foreach ($types as $type) { ?>
                                            <th class="border text-center"><?php echo $type ?> Amount</th>
                                            <?php } ?>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <?php foreach ($types as $type) { ?>
                                            <td class="text-right border" id="<?php echo $type ?>_partybal">0</td>
                                            <?php } ?>
                                        </tr>
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="d-flex" for="to_amount"><span
                                                style="text-transform: uppercase">{{ $cur_type }} </span>&nbsp;
                                        Amount<span class="tx-danger">*</span></label>
                                    <input type="text" class="form-control" id="to_amount" name="to_amount"
                                           placeholder="{{$cur_type}} amount">
                                    <span class="float-left tx-danger error_text to_amount_error"></span>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="d-flex" for="rate">Rate<span class="tx-danger">*</span></label>
                                    <input type="text" class="form-control" id="rate" name="rate"
                                           placeholder="Enter rate">
                                    <span class="float-left tx-danger error_text rate_error"></span>
                                </div>
                            </div>
                            <div class="form-group mb-lg-0 mt-4" style="margin: 15px;">
                                <i class="fa fa-xmark calc"></i>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="d-flex" for="from_amount">INR Amount<span
                                                class="tx-danger">*</span></label>
                                    <input type="text" class="form-control" id="from_amount" name="from_amount"
                                           placeholder="inr amount">
                                    <span class="float-left tx-danger error_text from_amount_error"></span>
                                </div>
                            </div>
                            <div class="col-lg-3 text-center align-self-center">
                                <div class="form-check">
                                    <button type="submit" class="btn btn-danger" id="submit">Submit</button>
                                </div>
                            </div>
                            <div class="col-md-6 mt-2 col-lg-6 col-sm-6 text-right">
                                <div class="error-msg tx-danger"></div>
                                <div class="form_proccessing text-center tx-success float-right"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        @php
            $todayDate = now()->format('Y-m-d');
        @endphp
        <div class="col-md-3">
            <div class="form-group mb-lg-0">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            From Date:
                        </div>
                    </div>
                    <input class="form-control fc-datepicker fromdate" id="fromdate" name="fromdate"
                           placeholder="YYYY-MM-DD" type="text" autocomplete="off" value="{{ $todayDate }}">
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
                    <input class="form-control fc-datepicker todate" id="todate" name="todate" placeholder="YYYY-MM-DD"
                           type="text" autocomplete="off" value="{{ $todayDate }}">
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group mb-lg-0">
                <select class="form-control type" id="user" name="user" style="text-transform: uppercase;">
                    <option value="" disabled selected>Select User</option>
                    @if(@$users)
                        @foreach($users as $user)
                            <option value="{{ $user->user_name }}">{{ $user->user_name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
        </div>
        <div class="col-md-2 d-flex justify-content-center">
            <div class="form-group mb-lg-0">
                <div class="input-group">
                    <button type="submit" class="btn btn-primary" id="apply_filter"><i class="fe fe-filter mr-1"></i>
                        Filter
                    </button>
                    <button type="reset" class="btn btn-danger ml-2" id="apply_reset">Clear</button>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-group mb-lg-0">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">
                            AVG
                        </div>
                    </div>
                    <input class="form-control" id="avg" type="text" val="" readonly>
                </div>
            </div>
        </div>
        <div class="col-lg-12">
            <div class="card custom-card">
                <div class="card-header-divider">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table data-table table-striped table-hover table-fw-widget"
                                   id="table_list_data" width="100%">
                                <thead>
                                <tr>
                                    <th style="width: 2%;">Sr</th>
                                    <th style="width: 10%;">Date</th>
                                    <th style="width: 5%">User</th>
                                    <th style="width: 15%;">Party Name</th>
                                    <th style="width: 13%;">{{$cur_type}} Amount</th>
                                    <th>Note</th>
                                    <th style="width: 13%;">INR Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td>Total</td>
                                    <td colspan="3"></td>
                                    <td class="text-right" id="cur_total"></td>
                                    <td colspan="1"></td>
                                    <td class="text-right" id="inr_total"></td>
                                </tr>
                                </tfoot>
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
                data: function (params) {
                    return {
                        // type: 'with_exch',
                        searchTerm: params.term, // search term
                    };
                },
                processResults: function (response) {
                    return {
                        results: response
                    };
                },
                cache: true
            }
        }).on('change', function () {
            var selectedOption = $(this).find('option:selected');
            var selectedId = selectedOption.val();
            <?php foreach ($types as $type) { ?>
            $("#<?php echo $type ?>_partybal").html(0);
            $("#<?php echo $type ?>_partybal").removeAttr('style');
            <?php } ?>
            $.ajax({
                url: "{{ url('getPartyBal') }}",
                type: "post",
                dataType: 'json',
                data: {
                    id: selectedId
                },
                success: function (response) {
                    if (response.st === 'success') {
                        var redstyle = {
                            backgroundColor: "#f9aeac",
                            color: "black",
                            fontWeight: "500"
                        };
                        var greenstyle = {
                            backgroundColor: "#93f992",
                            color: "black",
                            fontWeight: "500"
                        };
                        var zerostyle = {
                            color: "black",
                            fontWeight: "500"
                        }
                        <?php foreach ($types as $type) { ?>
                        $("#<?php echo $type ?>_partybal").html(response.data.<?php echo $type ?>_partybal);
                        var amount_type = response.data.<?php echo $type ?>_partybal;
                        if (amount_type < 0) {
                            $('#<?php echo $type ?>_partybal').css(redstyle);
                        }
                        if (amount_type > 0) {
                            $('#<?php echo $type ?>_partybal').css(greenstyle);
                        }
                        if (amount_type == 0) {
                            $('#<?php echo $type ?>_partybal').css(zerostyle);
                        }
                        <?php } ?>
                    } else {
                        <?php foreach ($types as $type) { ?>
                        $("#<?php echo $type ?>_partybal").html(0);
                        <?php } ?>
                    }
                },
                error: function (xhr, status, error) {
                    alert('Error');
                }
            });
        });

        $('#to_amount').keyup(function () {
            var to_amount = $('#to_amount').val();
            var rate = $('#rate').val();
            var from_amount = $('#from_amount').val();
            if (rate != '' && to_amount != '') {
                var ans = Math.round(to_amount * rate);
                $('#from_amount').val(ans);
            } else {
                $('#from_amount').val('');
            }
        });

        $('#rate').keyup(function () {
            var to_amount = $('#to_amount').val();
            var rate = $('#rate').val();
            var from_amount = $('#from_amount').val();
            if (rate !== '' && to_amount !== '') {
                var ans = Math.round(to_amount * rate);
                $('#from_amount').val(ans);
            } else {
                $('#from_amount').val('');
            }
        });

        $('#submit').click(function () {
            var party = $('#party').val();
            if (party === '') {
                alert("Please select party");
                return false;
            }
            $('#save_data').prop('disabled', true);
            $('.error-msg').html('');
            $('.form_proccessing').html('Please wait...');

            var rate = $('#rate').val();

            if ('{{$convert_type}}' === 'purchase') {
                var from_amount = $('#from_amount').val();
                var to_amount = $('#to_amount').val();
            }
            if ('{{$convert_type}}' === 'sales') {
                var to_amount = $('#from_amount').val();
                var from_amount = $('#to_amount').val();
            }

            $.ajax({
                type: "POST",
                url: "{{ url ('submit_convert') }}",
                data: {
                    party: party,
                    from_amount: from_amount,
                    rate: rate,
                    to_amount: to_amount,
                    convert_type: '{{$convert_type}}',
                    from_currency: '{{ $fromCurrency }}',
                    to_currency: '{{ $toCurrency }}'
                },
                success: function (response) {
                    if (response.st === 'success') {
                        var swal_html = '<style>td{text-align:left;padding:8px;}.swal2-title{padding:0px}.swal2-popup{width:30em;}</style><div style=text-align:-webkit-center;font-size:15px;><table border="1"><tr><td>From Srn </td><td>:-</td><td>' + response.msg.from_srn + '</td></tr><tr><td>To Srn </td><td>:-</td><td>' + response.msg.to_srn + '</td></tr><tr><td>Date</td><td>:-</td><td>' + response.msg.date + '</td></tr><tr><td>Party</td><td>:-</td><td>' + response.msg.party + '</td></tr><tr><td>Amount</td><td>:-</td><td>' + response.msg.amount + '</td></tr><tr><td>Notes</td><td>:-</td><td>' + response.msg.note + '</td></tr><tr><td>User</td><td>:-</td><td>' + response.msg.user + '</td></tr></table></div>'
                        Swal.fire({
                            title: '<div class="container" style="margin-top:10px;"><div class="custom-swal"><div class="custom-swal-icon"><i class="fa fa-arrow-right-arrow-left" style="color:green;"></i></div><div class="custom-content"> {{ ucfirst($titleParts[0]) }} {{strtoupper($titleParts[1])}} Success </div></div></div>',
                            html: swal_html,
                            allowOutsideClick: false,
                        }).then(() => {
                            location.reload()
                        });

                        $('.form_proccessing').html('');
                        $('#save_data').prop('disabled', false);
                    } else if (response.st == 'exch') {
                        alert(response.msg);
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
        });

        $(document).ready(function () {
            $('.fc-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                showOtherMonths: true,
                selectOtherMonths: true,
                maxDate: 0
            });
            load_data();
        });

        function load_data(fromdate = '{{$todayDate}}', todate = '{{$todayDate}}', user = '') {
            var cur_type = "{{ $cur_type }}";
            var amountColumnName = cur_type + '_amount';
            $('.data-table').DataTable({
                columnDefs: [{
                    className: 'dt-right',
                    targets: [4, 6]
                }],
                order: [
                    [0, 'desc']
                ],
                processing: true,
                serverSide: true,
                searching: false,
                bLengthChange: false,
                ajax: {
                    data: {
                        fromdate: fromdate,
                        todate: todate,
                        user: user,
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
                        data: 'party_name',
                        name: 'party_name'
                    },
                    {
                        data: amountColumnName,
                        name: amountColumnName
                    },
                    {
                        data: 'note',
                        name: 'note'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                ]
            });
            $.ajax({
                type: 'POST',
                url: "{{ url ('getpurchaseavg') }}",
                data: {
                    fromdate: fromdate,
                    todate: todate,
                    user: user,
                    convert_type: '{{ $convert_type }}',
                    from_currency: '{{ $fromCurrency }}',
                    to_currency: '{{ $toCurrency }}'
                },
                success: function (data) {
                    $('#cur_total').html(0);
                    $('#inr_total').html(0);
                    $('#cur_total').html(data.cur_total);
                    $('#inr_total').html(data.inr_total);
                    $('#avg').val(data.avg);
                }
            });
        }

        $('#apply_filter').click(function () {
            var fromdate = $('#fromdate').val();
            var todate = $('#todate').val();
            var user = $('#user').val();
            $('#table_list_data').DataTable().destroy();
            load_data(fromdate, todate, user);
        });

        $('#apply_reset').click(function () {
            $('#fromdate').val('{{$todayDate}}');
            $('#todate').val('{{$todayDate}}');
            $('#table_list_data').DataTable().destroy();
            $('#user').prop('selectedIndex', 0).trigger('change');
            load_data();
        })
    </script>
@endsection
