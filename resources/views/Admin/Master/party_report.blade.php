@extends('Admin.template')
@section('main-section')
    <?php $types = config('global.transction_type_list'); ?>
    <div class="page-header">
        <div>
            <h2 class="main-content-title tx-24 mg-b-5">Party Balance Report</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Master</li>
                <li class="breadcrumb-item active" aria-current="page">Balance report list</li>
            </ol>
        </div>
        <div class="btn btn-list">
            <a class="btn btn-outline-success rounded" href="{{ route('export_party_bal') }}"><i
                    class="far fa-file-excel"></i> Download Excel</a>
            <div id="myModal" class="modal fade" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
                 data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="screenshot_screen">
                            <div class="d-none" id="party_name"></div>
                            <h5 class="modal-title mt-3" style="font-size: 26px" id="exampleModalLabel"></h5>
                            <div class="modal-body d-flex justify-content-center align-items-center">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="card-body">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" id="save_data" type="submit" value="Submit"
                                    onclick="captureScreenshot()">Take Screenshot
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class=" row">
        <div class="col-lg-12">
            <div class="card custom-card">
                <div class="card-header-divider">
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" checked="checked" name="hide_zero"
                                   id="hide_zero_checkbox">
                            <label class="form-check-label mr-3" for="hide_zero_checkbox">Hide Zero</label>
                                ||
                            <a href="{{ url('group_wise_data') }}" class="ml-3">Party group wise report</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table data-table table-striped table-hover table-fw-widget"
                                   id="table_list_data" width="100%">
                                <thead>
                                <tr>
                                    <th style=width:10px;>SS</th>
                                    <th>Srn</th>
                                    <th>Party Name</th>
                                    <?php foreach ($types as $type) { ?>
                                    <th><?php echo $type ?></th>
                                    <?php } ?>
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

        $(document).ready(function () {
            load_data($("#hide_zero_checkbox").is(':checked'));
        });

        $("#hide_zero_checkbox").change(function () {
            $('#table_list_data').DataTable().destroy();
            load_data($("#hide_zero_checkbox").is(':checked'));
        });

        function load_data(hide_zero) {
            var column = [
                {
                    data: 'view',
                    name: 'view',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'srn',
                    name: 'srn'
                },
                {
                    data: 'party_name',
                    name: 'party_name'
                },

            ];
            var newtype = 3;
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
                aLengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                iDisplayLength: -1,
                columnDefs: [{
                    className: 'dt-right',
                    targets: newtypetarget
                },],
                processing: true,
                serverSide: true,
                ajax: {
                    data: {
                        hide_zero: hide_zero,
                    }
                },
                columns: column,
                createdRow: function (row, data, dataIndex) {
                    var newindex = 3;
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
                    var amount_type = data.<?php echo $type ?>_amount;
                    if (amount_type < 0) {
                        $(row).find('td:eq(' + newindex + ')').css(redstyle);
                    }
                    if (amount_type > 0) {
                        $(row).find('td:eq(' + newindex + ')').css(greenstyle);
                    }
                    if (amount_type === 0) {
                        $(row).find('td:eq(' + newindex + ')').css(zerostyle);
                    }
                    newindex++;
                    <?php } ?>
                }
            });

        }

        function view(view) {
            var srn = $(view).data('val');
            $.ajax({
                type: 'GET',
                url: "{{ url('party_report') }}",
                data: {
                    srn: srn,
                    single_party: 'single_party',
                },
                success: function (response) {
                    console.log(response)
                    if (response.st === 'success') {
                        var bookName = "{{ env('BOOK_NAME') }}";
                        $('.modal-title').html(bookName);
                        $('#party_name').empty().append(response.data.party_name);

                        var tableHTML = '<table class="table table-bordered"><tbody>';

                        // Add the first row with party_name and date
                        tableHTML += '<tr style="font-size:17px"><td><b>' + response.data.party_name + '</b></td><td><b>' + "{{ now()->format('d-m-Y') }}" + '</b></td></tr>';

                        // Iterate through dynamic currency keys and add rows to the table
                        for (var currency in response.data) {
                            if (response.data.hasOwnProperty(currency) && currency.endsWith('_amount')) {
                                var currencyCode = currency.split('_')[0].toUpperCase() + ' Balance'
                                var amount = response.data[currency];
                                var backgroundColor = '';

                                // Set background color based on the value of amount
                                if (amount < 0) {
                                    backgroundColor = 'background-color: #f9aeac;'; // Amount is negative
                                } else if (amount > 0) {
                                    backgroundColor = 'background-color: #93f992;'; // Amount is positive
                                }

                                // Add row to the table with the specified background color
                                tableHTML += '<tr><td>' + currencyCode + '</td><td style="' + backgroundColor + 'text-align: right;">' + amount + '</td></tr>';
                            }
                        }

                        // Close the table body and table
                        tableHTML += '</tbody></table>';

                        // Replace the content of the modal body with the new table
                        $('#myModal .modal-body').html(tableHTML);

                        $('#myModal').modal('show');
                    } else {
                        alert('failed');
                    }
                },
                error: function (error) {
                    $('#save_data').prop('disabled', false);
                    alert('Error');
                }
            });
        }

        function captureScreenshot() {
            var partyName = $('#party_name').text().trim();
            var todayDate = '{{ now()->format("d_m_Y") }}';
            var fileName = partyName + '_' + todayDate + '.jpg'; // Adjust the format as needed

            // Capture the screenshot of the modal body
            html2canvas(document.querySelector('.screenshot_screen')).then(function (canvas) {
                // Convert the canvas to an image and open it in a new window or save it as needed
                var imgData = canvas.toDataURL('image/jpeg'); // Change 'image/png' to 'image/jpeg'
                var link = document.createElement('a');
                link.href = imgData;
                link.download = fileName;
                link.click();
            });
        }
    </script>
@endsection
