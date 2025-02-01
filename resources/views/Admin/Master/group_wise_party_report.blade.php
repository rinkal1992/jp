@extends('Admin.template')
@section('main-section')
    <div class="page-header">
        <div>
            <h2 class="main-content-title tx-24 mg-b-5">Group Party Balance Report</h2>
            <ol class="breadcrumb">
                <li class="breadcrumb-item">Master</li>
                <li class="breadcrumb-item active" aria-current="page">Balance report list</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card custom-card">
                <div class="card-header-divider">
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" checked name="hide_zero"
                                   id="hide_zero_checkbox">
                            <label class="form-check-label mr-3" for="hide_zero_checkbox">Hide Zero</label>
                            ||
                            <a href="{{ url('party_report') }}" class="ml-3">Party report</a>
                        </div>
                        @if($grouped_transactions)
                            @foreach($grouped_transactions as $group)
                                @if(is_array($group))
                                    <div class="table-responsive">
                                        <h3>{{ $group['group_name'] }}</h3>
                                        <table class="table table-hover">
                                            <thead>
                                            <tr>
                                                <th style="width: 10%">SRN</th>
                                                <th style="width: 30%">Party Name</th>
                                                <th style="width: 20%; text-align: end">INR Amount</th>
                                                <th style="width: 20%; text-align: end">USD Amount</th>
                                                <th style="width: 20%; text-align: end">AED Amount</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($group as $transaction)
                                                @if(is_array($transaction))
                                                    <tr>
                                                        <td>{{ $transaction['srn'] }}</td>
                                                        <td>{{ $transaction['party_name'] }}</td>
                                                        <td style="text-align: end; background-color: {{ $transaction['inr_amount'] > 0 ? '#93f992' : ($transaction['inr_amount'] < 0 ? '#f9aeac' : 'transparent') }}">{{ $transaction['inr_amount'] }}</td>
                                                        <td style="text-align: end; background-color: {{ $transaction['usd_amount'] > 0 ? '#93f992' : ($transaction['usd_amount'] < 0 ? '#f9aeac' : 'transparent') }}">{{ $transaction['usd_amount'] }}</td>
                                                        <td style="text-align: end; background-color: {{ $transaction['aed_amount'] > 0 ? '#93f992' : ($transaction['aed_amount'] < 0 ? '#f9aeac' : 'transparent') }}">{{ $transaction['aed_amount'] }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            </tbody>
                                            <tfoot>
                                            <tr>
                                                <td colspan="2"></td>
                                                <td class="text-right"><b>Total : </b> {{ $group['total_inr'] }}</td>
                                                <td class="text-right"><b>Total : </b> {{ $group['total_usd'] }}</td>
                                                <td class="text-right"><b>Total : </b> {{ $group['total_aed'] }}</td>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('#hide_zero_checkbox').prop('checked', true);

            $('#hide_zero_checkbox').change();
        });
        $(document).on('change', '#hide_zero_checkbox', function () {
            var isChecked = $(this).prop('checked');

            var rows = $('table tbody tr');

            rows.each(function () {
                var row = $(this);
                if (isChecked) {
                    if (parseFloat(row.find("td:eq(2)").text().trim()) == 0 &&
                        parseFloat(row.find("td:eq(3)").text().trim()) == 0 &&
                        parseFloat(row.find("td:eq(4)").text().trim()) == 0) {
                        row.hide();
                    } else {
                        row.show();
                    }
                } else {
                    row.show();
                }
            });
        });
    </script>
@endsection
