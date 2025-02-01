<table>
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
                <td>{{ date('Y-m-d', strtotime($row['date'])) }}</td>
                <td>{{ $row['user_name'] }}</td>
                <td>{{ $row['note'] }}</td>
                @foreach ($types as $type)
                    <td>{{ $row[$type.'_debit'] }}</td>
                    <td>{{ $row[$type.'_credit'] }}</td>
                    <td>{{ $row[$type.'_cbalance'] }}</td>
                @endforeach
            </tr>
        @endforeach
    @endif
    </tbody>
</table>
