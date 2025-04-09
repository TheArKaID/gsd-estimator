<table>
    <thead>
        <tr>
            <th>Project Name</th>
            <th>Story ID</th>
            <th>Story Name</th>
            <th>Description</th>
            <th>Story Points</th>
            <th>Created At</th>
        </tr>
    </thead>
    <tbody>
        @foreach($storyPointsData as $item)
            <tr>
                <td>{{ $item['project_name'] }}</td>
                <td>{{ $item['id'] }}</td>
                <td>{{ $item['name'] }}</td>
                <td>{{ $item['description'] }}</td>
                <td>{{ $item['value'] }}</td>
                <td>{{ $item['created_at'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
