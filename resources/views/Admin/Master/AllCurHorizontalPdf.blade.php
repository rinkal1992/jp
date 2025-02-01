<!DOCTYPE html>
<html>

<head>
    <title>Statements</title>
</head>
<style>
    body {
        font-family: Arial, Helvetica, serif;
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
<h2 style="margin-bottom: 5px;">All Currency Statement : {{ env('BOOK_NAME') }}</h2>
<span>Master / Transaction History</span>
<h4> All Statement of {{ $party_name }}
    @if(@$from_date && @$to_date)
        {{ "From $from_date To $to_date" }}
    @endif
</h4>
<table style="border:1px solid #e1e6f1; width:100%; border-collapse: collapse !important;">
    <thead>
    <tr>
        <th rowspan="2">Sr</th>
        <th rowspan="2">Date</th>
        <th rowspan="2">User</th>
        <th rowspan="2">Description</th>
        @foreach($types as $type)
            <th colspan="3" style="text-align: center">{{ strtoupper($type) }}</th>
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
    @if($data)
        @foreach ($data as $row)
            <tr>
                <td>{{ $row['srn'] }}</td>
                <td style="width:60px;">{{ date('Y-m-d', strtotime($row['date'])) }}</td>
                <td>{{ $row['user_name'] }}</td>
                <td>{{ $row['note'] }}</td>
                @foreach ($types as $type)
                    <td style="text-align: right;width:35px;">{{ $row[$type.'_debit'] }}</td>
                    <td style="text-align: right;width:35px;">{{ $row[$type.'_credit'] }}</td>
                    <td style="text-align: right;width:35px;">{{ $row[$type.'_cbalance'] }}</td>
                @endforeach
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="4" style="text-align: center;">No Entries</td>
            @foreach($types as $type)
                <td></td>
                <td></td>
                <td></td>
            @endforeach
        </tr>
    @endif
    </tbody>
</table>
</body>

</html>
