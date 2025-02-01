<?php $types = config('global.transction_type_list'); ?>
<div class="main-sidebar main-sidebar-sticky side-menu">
    <div class="main-sidebar-body">
        <ul class="nav">
            <li class="nav-label">MENU</li>
            <li class="nav-item {{ @$title == 'party_list' ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/party_list') }}"><i class="fa fa-users"></i><span class="sidemenu-label">Party</span></a>
            </li>
            <?php foreach ($types as $type) { ?>
                <li class="nav-item {{ @$title == 'transaction_'.$type.'' ? 'active' : '' }}">
                    <a class="nav-link" href=" {{ url('transaction/'.$type.'') }}"><i class="fas fa-exchange"></i><span class="sidemenu-label">Transaction <span style="text-transform:uppercase"><?php echo $type ?></span></span></a>
                </li>
            <?php } ?>
            <li class="nav-item {{ @$title == 'exchange_currency' ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/exchange_currency') }}"><i class="fa-solid fa-money-bills"></i><span class="sidemenu-label">Exchange Currency</span></a>
            </li>
            @if($types)
                @foreach($types as $type)
                    @if($type != 'inr')
                        <li class="nav-item {{ @$title == "purchase_$type" ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('/convert/purchase/'.$type.'') }}"><i class="fa-solid fa-money-bills"></i><span class="sidemenu-label">Purchase <span style="text-transform:uppercase">{{$type}}</span></span></a>
                        </li>
                        <li class="nav-item {{ @$title == "sales_$type" ? 'active' : '' }}">
                            <a class="nav-link" href="{{ url('/convert/sales/'.$type.'') }}"><i class="fa-solid fa-money-bills"></i><span class="sidemenu-label">Sales <span style="text-transform:uppercase">{{$type}}</span></span></a>
                        </li>
                     @endif
                @endforeach
            @endif
            <li class="nav-item {{ @$title == 'report_list' ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/report_list') }}"><i class="far fa-file-text"></i><span class="sidemenu-label">Report</span></a>
            </li>
            <li class="nav-item {{ @$title == 'statement_list' ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/statement_list') }}"><i class="fa fa-bank"></i><span class="sidemenu-label">Statement</span></a>
            </li>
            <li class="nav-item {{ @$title == 'all_statement_list' ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/all_statement_list') }}"><i class="fa fa-bank"></i><span class="sidemenu-label">All Currency Statement</span></a>
            </li>
            <li class="nav-item {{ @$title == 'party_report' ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/party_report') }}"><i class="far fa-file-text"></i><span class="sidemenu-label">Party Balance Report</span></a>
            </li>
            <li class="nav-item {{ @$title == 'commission_transfer' ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/commission_transfer') }}"><i class="fas fa-exchange"></i><span class="sidemenu-label">Commission Transfer</span></a>
            </li>
            <li class="nav-item {{ @$title == 'statement_checkup' ? 'active' : '' }}">
                <a class="nav-link" href="{{ url('/statement_checkup') }}"><i class="far fa-check-circle"></i><span class="sidemenu-label">Statement Checkup</span></a>
            </li>
        </ul>
    </div>
</div>
