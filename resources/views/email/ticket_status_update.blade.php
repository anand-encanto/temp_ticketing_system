<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Status Updated</title>
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
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .header {
            background-color: #28a745;
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

        .ticket-info {
            background-color: #f3f4f6;
            border-left: 4px solid #28a745;
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
            background-color: #28a745;
            color: #ffffff !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            margin-top: 20px;
        }

        .btn:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <h1>Ticket Status Updated</h1>
        </div>

        <div class="content">
            <p>Hi,</p>
            <p>The status of your ticket has been updated. Here are the details:</p>

            <div class="ticket-info">
                <p><strong>Ticket ID:</strong> #{{ $ticket_id }}</p>
                <p><strong>Title:</strong> {{ $ticket_title }}</p>
                <p><strong>Current Status:</strong> {{ $ticket_status }}</p>
                <p><strong>Assigned To:</strong> {{ $assignee_name }}</p>
                <p><strong>Description:</strong> {{ $ticket_description }}</p>
            </div>

            @php
            // $ticketUrl = config('app.frontend_url') . '/tickets/' . $ticket_id;
            $ticketUrl = rtrim(env('FRONTEND_URL', config('app.url')), '/') . '/tickets/' . $ticket_id;
            @endphp

            <a href="{{ $ticketUrl }}" class="btn">
                View Ticket #{{ $ticket_id }}
            </a>

            <p style="margin-top:15px;font-size:13px;color:#555;">
                If the button doesn’t work, copy and paste this link into your browser:<br>
                <a href="{{ $ticketUrl }}">{{ $ticketUrl }}</a>
            </p>
        </div>

        <div class="footer">
            <p>Thank you,<br>McDonald's Team</p>
        </div>
    </div>

</body>

</html>