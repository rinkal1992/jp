<!DOCTYPE html>
<html>

<head>
    <title>Statements</title>
</head>
<style>
    body {
        font-family: Arial, Helvetica;
        font-size: 0.70rem;
    }

    table tr td {
        border: 1px solid #e1e6f1;
        padding: 3px;
    }

    table tr th {
        border: 1px solid #e1e6f1;
    }
</style>

<body>
    <h2 style="margin-bottom: 5px;">Statement : {{ env('BOOK_NAME') }}</h2>
    <span>Master / Transaction History</span>
    <h4> <span style="text-transform: uppercase;">{{ $type }}</span> Statement of {{ $party_name }}
        <?php if (@$from_date && @$to_date) {
            echo "From" . ' ' . $from_date . ' ' . "To" . ' ' . $to_date;
        } ?>
    </h4>
    <table style="border:1px solid #e1e6f1; width:100%; border-collapse: collapse !important;">
        <tr>
            <th style="width:20px;">SR</th>
            <th style="width:70px;">Date</th>
            <th style="width:30px;">User</th>
            <?php if (isset($show_party)) { ?>
                <th style="width: 10%;">Party</th>
            <?php } ?>
            <th style="width:100%;">Description</th>
            <th style="width:80px;">Debit</th>
            <th style="width:80px;">Credit</th>
            <th style="width:80px;">Closing Balance</th>
        </tr>
        <?php if ($statements) { ?>
            @foreach($statements as $type)
            <?php $date = strtotime($type->date);
            $newDate = date('Y-m-d', $date); ?>
            <tr>
                <td>{{ $type->srn }}</td>
                <td>{{ $newDate }}</td>
                <td>{{ $type->user_name }}</td>
                <?php if (isset($show_party)) { ?>
                    <td>{{ $type->opp_party_name }}</td>
                <?php } ?>
                <td>{{ $type->note }}</td>
                <td style="text-align:right;">{{ $type->dr_party_balance }}</td>
                <td style="text-align:right;">{{ $type->cr_party_balance }}</td>
                <td style="text-align:right;">{{ $type->cbalance }}</td>
            </tr>
            @endforeach
        <?php } else { ?>
            <tr>
                <td colspan="7" style="text-align:center;">No Entries</td>
            </tr>
        <?php } ?>
    </table>
</body>

</html>