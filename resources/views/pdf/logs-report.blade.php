<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Activity Logs</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #0B2B4F; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #0B2B4F; color: white; }
    </style>
</head>
<body>
    <h1>System Activity Logs</h1>
    <p>Generated: {{ now()->format('Y-m-d H:i:s') }}</p>
    <table>
        <thead><tr><th>Timestamp</th><th>User</th><th>Action</th><th>Details</th></tr></thead>
        <tbody>
            @foreach($logs as $log)
            <tr>
                <td>{{ $log->created_at }}</td>
                <td>{{ $log->username }}</td>
                <td>{{ $log->action }}</td>
                <td>{{ $log->details }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
