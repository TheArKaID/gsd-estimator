<table>
    <thead>
        <tr>
            <th>Project Name</th>
            <th>Project Type</th>
            <th>Project Clarity</th>
            <th>Team Size</th>
            <th>Velocity</th>
            <th>Story Points</th>
            <th>GSD Estimation (days)</th>
            <th>Previous Estimation (days)</th>
            <th>Actual Effort (days)</th>
            <th>Difference between Estimates (%)</th>
            <th>More Accurate Estimation</th>
            <th>GSD Accuracy (%)</th>
            <th>GSD Estimation Type</th>
            <th>Previous Accuracy (%)</th>
            <th>Previous Estimation Type</th>
        </tr>
    </thead>
    <tbody>
        @foreach($projectData as $project)
            <tr>
                <td>{{ $project['name'] }}</td>
                <td>{{ $project['project_type'] }}</td>
                <td>{{ $project['project_clarity'] }}</td>
                <td>{{ $project['team_size'] }}</td>
                <td>{{ $project['velocity'] }}</td>
                <td>{{ $project['total_story_points'] }}</td>
                <td>{{ $project['gsd_estimation'] }}</td>
                <td>{{ $project['previous_estimation'] }}</td>
                <td>{{ $project['actual_effort'] }}</td>
                <td>
                    @if($project['vs_estimation_percent'] !== null)
                        {{ $project['vs_estimation_percent'] }}%
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if($project['actual_effort'])
                        @if($project['vs_estimation_better'] === true)
                            GSD (more accurate by {{ $project['previous_accuracy_percent'] - $project['gsd_accuracy_percent'] }}%)
                        @elseif($project['vs_estimation_better'] === false)
                            Previous (more accurate by {{ $project['gsd_accuracy_percent'] - $project['previous_accuracy_percent'] }}%)
                        @else
                            Equal accuracy
                        @endif
                    @else
                        N/A (no actual data)
                    @endif
                </td>
                <td>
                    @if($project['gsd_accuracy_percent'] !== null)
                        {{ $project['gsd_accuracy_percent'] }}%
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if($project['gsd_accuracy_type'])
                        {{ ucfirst($project['gsd_accuracy_type']) }}estimated
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if($project['previous_accuracy_percent'] !== null)
                        {{ $project['previous_accuracy_percent'] }}%
                    @else
                        N/A
                    @endif
                </td>
                <td>
                    @if($project['previous_accuracy_type'])
                        {{ ucfirst($project['previous_accuracy_type']) }}estimated
                    @else
                        N/A
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
