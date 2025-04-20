<table>
    <thead>
        <tr>
            <th>Project Name</th>
            <th>Factor Name</th>
            <th>Criteria Name</th>
        </tr>
    </thead>
    <tbody>
        @foreach($globalFactorsData as $factor)
            <tr>
                <td>{{ $factor['project_name'] }}</td>
                <td>{{ $factor['factor_name'] }}</td>
                <td>{{ $factor['criteria_name'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
