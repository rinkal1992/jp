@extends('Admin.template')
@section('main-section')
    <?php $types = config('global.transction_type_list'); ?>
    <style>
        .calc {
            border: 1px solid #ced4da;
            border-radius: 5px;
            cursor: pointer;
            padding: 10px;
            width: 20%;
            text-align: center;
        }

        .calc:hover {
            background-color: #007bff;
            transition: 0.5s;
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
    </style>
    <div class="page-header">
        <div>
            <h2 class="main-content-title tx-24 mg-b-5">Exchange Currency</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Master</li>
                <li class="breadcrumb-item active" aria-current="page">currency converter</li>
            </ol>
        </div>
    </div>
    <form action="{{ url('submit_exchange_currency') }}" class="ajax-form-submit" id="cform" method="post"
          enctype="multipart/form-data">
        @csrf
        <div class="row" style="display: flex;justify-content: center;">
            <div class="col-lg-9 col-12">
                <div class="card custom-card">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Currency Converter</h5>
                    </div>
                    <div class="card-body">
                        <div class="row" style="display: flex;justify-content: center;">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="d-flex" for="party">Select Party<span
                                            class="tx-danger">*</span></label>
                                    <select class="form-control partyselect2" name="party" id="party">
                                        <option value=""></option>
                                    </select>
                                    <span class="float-left tx-danger error_text party_error"></span>
                                </div>
                            </div>
                            <div class="col-lg-8">
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
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="d-flex" for="from_currency">From (Sell)<span
                                            class="tx-danger">*</span></label>
                                    <select class="form-control type" id="from_currency" name="from_currency"
                                            onchange="fromCurrency(this.value)" style="text-transform: uppercase;">
                                        <option value="" disabled selected>select currency</option>
                                        <?php foreach ($types as $type) { ?>
                                        <option value="<?php echo $type ?>"><?php echo $type ?></option>
                                        <?php } ?>
                                    </select>
                                    <span class="float-left tx-danger error_text from_currency_error"></span>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="float-left" for="from_amount">Amount<span
                                            class="tx-danger">*</span></label>
                                    <input type="text" class="form-control" id="from_amount" name="from_amount"
                                           placeholder="Enter amount">
                                    <span class="float-left tx-danger error_text from_amount_error"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 d-flex align-items-center justify-content-center">
                                <i class="fa fa-up-down p-3" onclick="swapit()" style="cursor:pointer;"></i>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <label class="d-flex" for="rate_amount">Rate<span class="tx-danger">*</span></label>
                                    <input type="text" class="form-control" id="rate_amount" name="rate_amount"
                                           placeholder="Enter rate">
                                    <span class="float-left tx-danger error_text rate_amount_error"></span>
                                </div>
                            </div>
                            <div class="col-lg-4 ">
                                <div class="form-group">
                                    <label class="d-flex">Select Calculation<span class="tx-danger">*</span></label>
                                    <i class="fa fa-xmark calc" onclick="multiplication()"></i>
                                    <i class="fa fa-divide calc" onclick="division()"></i>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="d-flex" for="to_currency">To (Buy)<span
                                            class="tx-danger">*</span></label>
                                    <select class="form-control type" id="to_currency" name="to_currency"
                                            onchange="toCurrency(this.value)" style="text-transform: uppercase;">
                                        <option value="" disabled selected>select currency</option>
                                        <?php foreach ($types as $type) { ?>
                                        <option value="<?php echo $type ?>"><?php echo $type ?></option>
                                        <?php } ?>
                                    </select>
                                    <span class="float-left tx-danger error_text to_currency_error"></span>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="float-left" for="to_amount">Amount<span
                                            class="tx-danger">*</span></label>
                                    <input type="text" class="form-control" id="to_amount" placeholder="Enter amount"
                                           name="to_amount">
                                    <span class="float-left tx-danger error_text to_amount_error"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="error-msg tx-danger"></div>
                    <div class="form_proccessing text-center tx-success float-left"></div>
                    <div class="modal-footer" style="justify-content: center;">
                        <button class="btn btn-primary" id="save_data" type="submit" value="Submit">Submit</button>
                        <button type="reset" id="reset" class="btn btn-danger">Reset</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
                <select name="currency_dropdown" id="currency_dropdown" class="form-control"
                        style="text-transform: uppercase;">
                    <option value="" disabled selected>select currency</option>
                    @foreach (@$dropdownOptions as $option)
                        <option value="{{ $option['info'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
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
        <div class="col-md-2">
            <div class="form-group mb-lg-0">
                <div class="input-group">
                    <button type="submit" class="btn btn-primary" id="apply_filter"><i class="fe fe-filter mr-1"></i>
                        Filter
                    </button>
                    <button type="reset" class="btn btn-danger ml-2" id="apply_reset">Clear</button>
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
                                    <th style="width: 14%;">Date</th>
                                    <th style="width: 4%">User</th>
                                    <th style="width: 10%;">Party Name</th>
                                    <th>Note</th>
                                    <th style="width: 8%">Actual Rate</th>
                                    <?php foreach ($types as $type) { ?>
                                    <th style="width: 13%;"><?php echo $type ?> Amount</th>
                                    <?php } ?>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="6" class="text-right">Total</td>
                                    <?php foreach ($types as $type) { ?>
                                    <td class="text-right" id="<?php echo $type ?>_total"></td>
                                    <?php } ?>
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
                        searchTerm: params.term,
                        cr_party: $('select[name="cr_party"]').val(),
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
                    if (response.st == 'success') {
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

        $('#reset').click(function () {
            $(".partyselect2").val('').trigger('change');
            $('.form_proccessing').html('');
            $('#save_data').prop('disabled', false);
            $("#cform").trigger("reset");
        });

        function fromCurrency(type) {
            if (type != '') {
                $("#to_currency option[value='" + type + "']").hide();
                $("#to_currency option[value!='" + type + "']").show();
            }
        }

        function toCurrency(type) {
            if (type != '') {
                $("#from_currency option[value='" + type + "']").hide();
                $("#from_currency option[value!='" + type + "']").show();
            }
        }

        function swapit() {
            var second = document.getElementById("to_currency");
            var first = document.getElementById("from_currency");
            var third = document.getElementById("to_amount");
            var four = document.getElementById("from_amount");
            var temp;
            var temp__1;
            temp__1 = four.value;
            four.value = third.value;
            third.value = temp__1;
            temp = second.value;
            second.value = first.value;
            first.value = temp;
        }

        function multiplication() {
            var from_amount = $('#from_amount').val();
            var to_amount = $('#to_amount').val();
            var rate = $('#rate_amount').val();
            if (rate !== '' && from_amount !== '') {
                var ans = Math.round(from_amount * rate);
                $('#to_amount').val(ans);
            } else if (rate !== '' && to_amount !== '') {
                var ans = Math.round(to_amount * rate);
                $('#from_amount').val(ans);
            } else {
                var ans = Math.round(from_amount * rate);
                $('#to_amount').val(ans);
            }
        }

        function division() {
            var from_amount = $('#from_amount').val();
            var to_amount = $('#to_amount').val();
            var rate = $('#rate_amount').val();
            if (rate !== '' && from_amount !== '') {
                var ans = Math.round(from_amount / rate);
                $('#to_amount').val(ans);
            } else if (rate !== '' && to_amount !== '') {
                var ans = Math.round(to_amount / rate);
                $('#from_amount').val(ans);
            } else {
                var ans = Math.round(from_amount / rate);
                $('#to_amount').val(ans);
            }
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
                    console.log(response);
                    if (response.st === 'success') {
                        var from__Currency = document.getElementById('from_currency').value.toUpperCase();
                        var to__Currency = document.getElementById('to_currency').value.toUpperCase();
                        var bookName = "{{ env('BOOK_NAME') }}";

                        var swal_html = '<div class="" style="margin-top:10px;"><div class="custom-swal" style="font-size: 20px;"><div class="custom-swal-icon"><i class="fa fa-arrow-right-arrow-left fa-flip" style="color:green;"></i></div><div class="custom-content">Sell ' + from__Currency + ' & Buy ' + to__Currency + ' : ' + bookName + '</div></div></div><style>td{text-align:left;padding:8px;}.swal2-title{padding:0px}.swal2-popup{width:28em;}</style><div style="text-align:-webkit-center;font-size:15px;"><table border="1"><tr><td>Srn</td><td>:</td><td>' + response.msg.from_srn + '</td><td rowspan="2"></td><td>' + response.msg.date + '</td></tr><tr><td width="70px;">Party</td><td>:</td><td>' + response.msg.party + '</td><td style="text-align:right;"> Amount = ' + response.msg.from_amount + '</td></tr><tr><td>Notes</td><td>:</td><td colspan="4">' + response.msg.note + '</td></tr><tr><td>User</td><td>:</td><td colspan="4">' + response.msg.user + '</td></tr></table></div>';

                        swal_html += '<div style="margin-top: 20px;"></div>';

                        var second_table = '<div class="" style="margin-top:10px;"><div class="custom-swal" style="font-size: 20px;"><div class="custom-swal-icon"><i class="fa fa-arrow-right-arrow-left fa-flip" style="color:green;"></i></div><div class="custom-content">Sell ' + to__Currency + ' & Buy ' + from__Currency + ' : ' + bookName + '</div></div></div><div style="text-align:-webkit-center;font-size:15px;"><table border="1"><tr><td>Srn</td><td>:</td><td>' + response.msg.to_srn + '</td><td rowspan="2"></td><td>' + response.msg.date + '</td></tr><tr><td width="70px;">Party</td><td>:</td><td>' + response.msg.party + '</td><td style="text-align:right;">Amount = ' + response.msg.amount + '</td></tr><tr><td>Notes</td><td>:</td><td colspan="4">' + response.msg.note + '</td></tr><tr><td>User</td><td>:</td><td colspan="4">' + response.msg.user + '</td></tr></table></div>';

                        swal_html += second_table;

                        Swal.fire({
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
            return false;
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

        function load_data(fromdate = '{{$todayDate}}', todate = '{{$todayDate}}', currency_dropdown = '', user = '') {
            var column = [{
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
                    data: 'note',
                    name: 'note'
                },
                {
                    data: 'actual_rate',
                    name: 'actual_rate'
                },
            ];
            var newtype = 6;
            var newtypetarget = [];
            <?php foreach ($types as $type) { ?>
            var type = {
                data: '<?php echo $type ?>_amount',
                name: '<?php echo $type ?>_amount',
            }
            newtypetarget.push(newtype);
            newtype++;
            column.push(type);
            <?php } ?>
            $('.data-table').DataTable({
                columnDefs: [{
                    className: 'dt-right',
                    targets: newtypetarget
                }],
                order: [
                    [1, 'desc']
                ],
                iDisplayLength: 25,
                processing: true,
                serverSide: true,
                searching: false,
                bLengthChange: false,
                ajax: {
                    data: {
                        fromdate: fromdate,
                        todate: todate,
                        currency_dropdown: currency_dropdown,
                        user: user,
                    }
                },
                columns: column
            });
            $.ajax({
                type: 'POST',
                url: "{{ url ('getExchTotal') }}",
                data: {
                    fromdate: fromdate,
                    todate: todate,
                    currency_dropdown: currency_dropdown,
                    user: user,
                },
                success: function (data) {
                    <?php foreach ($types as $type) { ?>
                    $("#<?php echo $type ?>_total").html(0);
                    <?php } ?>
                    $.each(data, function () {
                        <?php foreach ($types as $type) { ?>
                        $("#<?php echo $type ?>_total").html(data.<?php echo $type ?>_total);
                        <?php } ?>
                    });
                }
            });
        }

        $('#apply_filter').click(function () {
            var fromdate = $('#fromdate').val();
            var todate = $('#todate').val();
            var currency_dropdown = $('#currency_dropdown').val();
            var user = $('#user').val();
            $('#table_list_data').DataTable().destroy();
            load_data(fromdate, todate, currency_dropdown, user);
        });

        $('#apply_reset').click(function () {
            $('#fromdate').val('{{$todayDate}}');
            $('#todate').val('{{$todayDate}}');
            $('#table_list_data').DataTable().destroy();
            $('#currency_dropdown').prop('selectedIndex', 0).trigger('change');
            $('#user').prop('selectedIndex', 0).trigger('change');
            load_data();
        })
    </script>
@endsection
