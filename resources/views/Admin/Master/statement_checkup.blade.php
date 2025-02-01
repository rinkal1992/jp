@extends('Admin.template')
@section('main-section')
<?php $types = config('global.transction_type_list'); ?>
<div class="page-header">
    <div>
        <h2 class="main-content-title tx-24 mg-b-5">Transaction Checkup</h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item">Master</li>
            <li class="breadcrumb-item active" aria-current="page">check</li>
        </ol>
    </div>
</div>
<?php foreach ($types as $type) { ?>
    <div><span style="text-transform: uppercase;"><?php echo $type ?> </span> checkup</div>
    <div class="row">
        <div class="col-lg-12">
            <div class="card custom-card">
                <div class="card-header-divider">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table data-table transaction_<?php echo $type ?> table-striped table-hover table-fw-widget" id="transaction_<?php echo $type ?>" width="100%">
                                <thead>
                                    <tr>
                                        <th>sr</th>
                                        <th>dr party name</th>
                                        <th>cr party name</th>
                                        <th>Prev closing balance</th>
                                        <th>Transfer Amount</th>
                                        <th>is debit</th>
                                        <th>current Closing balance</th>
                                        <th>actual closing balance</th>
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
@endsection
@section('scripts')
<script type="text/javascript">
    $(document).ready(function() {
        load_data('');
    });

    function load_data() {
        <?php foreach ($types as $type) { ?>
            $('.transaction_<?php echo $type ?>').DataTable({
                oLanguage: {
                    "sEmptyTable": "No Data"
                },
                columnDefs: [{
                    className: 'dt-right',
                    targets: [3, 4, 6, 7]
                }, ],
                processing: true,
                serverSide: true,
                ajax: {
                    data: {
                        type: '<?php echo $type ?>'
                    }
                },
                columns: [{
                        data: 'state_key.srn',
                        name: 'state_key.srn'
                    },
                    {
                        data: 'state_key.dr_party_name',
                        name: 'state_key.dr_party_name'
                    },
                    {
                        data: 'state_key.cr_party_name',
                        name: 'state_key.cr_party_name'
                    },
                    {
                        data: 'cbalance',
                        name: 'cbalance'
                    },
                    {
                        data: 'state_key.transfer_amount',
                        name: 'state_key.transfer_amount'
                    },
                    {
                        data: 'state_key.is_debit',
                        name: 'state_key.is_debit'
                    },
                    {
                        data: 'state_key.cbalance',
                        name: 'state_key.cbalance'
                    },
                    {
                        data: 'state_key.actual_cbalance',
                        name: 'state_key.actual_cbalance'
                    }
                ]
            });
        <?php } ?>
    }
</script>
@endsection