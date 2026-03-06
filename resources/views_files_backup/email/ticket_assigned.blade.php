<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Assigned</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f7f8fa;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            background: #ffffff;
            max-width: 600px;
            margin: 40px auto;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background-color: #007bff;
            color: #ffffff;
            text-align: center;
            padding: 20px 10px;
        }
        .header h1 {
            font-size: 22px;
            margin: 0;
        }
        .content {
            padding: 25px;
        }
        .content h2 {
            font-size: 18px;
            color: #333;
        }
        .ticket-info {
            background-color: #f3f4f6;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
        }
        .ticket-info p {
            margin: 5px 0;
        }
        .footer {
            background-color: #f1f1f1;
            text-align: center;
            color: #777;
            font-size: 14px;
            padding: 15px;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>🎫 New Ticket Assigned</h1>
    </div>

    <div class="content">
        <p>Hi <strong>{{ $assignee_name }}</strong>,</p>
        <p>A new ticket has been assigned to you. Please review the details below:</p>

        <div class="ticket-info">
            <p><strong>Ticket ID:</strong> #{{ $ticket_id }}</p>
            <p><strong>Title:</strong> {{ $ticket_title }}</p>
            <p><strong>Description:</strong> {{ $ticket_description }}</p>
            <p><strong>Expected Resolution Time:</strong> {{ \Carbon\Carbon::parse($expected_resolution_time)->format('d M Y, h:i A') }}</p>
            <p><strong>Assigned By:</strong> {{ $assigned_by }}</p>
        </div>

    </div>

    <div class="footer">
        <p>Thank you,<br>McDonald's Team</p>
    </div>
</div>

</body>
</html>
