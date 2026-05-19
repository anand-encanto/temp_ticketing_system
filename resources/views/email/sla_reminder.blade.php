<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SLA Breach Alert</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f7f8fa;
            color: #333333;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            background: #ffffff;
            max-width: 600px;
            margin: 40px auto;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #eaeaea;
        }
        .header {
            background-color: #dc3545;
            color: #ffffff;
            text-align: center;
            padding: 24px 20px;
        }
        .header h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 20px;
        }
        .warning-text {
            background-color: #fff5f5;
            border-left: 4px solid #dc3545;
            color: #b02a37;
            padding: 15px;
            margin-bottom: 25px;
            border-radius: 4px;
            font-size: 14px;
            line-height: 1.5;
        }
        .ticket-info {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 20px;
            margin-top: 15px;
            border-radius: 8px;
        }
        .ticket-info h3 {
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 16px;
            color: #212529;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
        }
        .info-row {
            display: flex;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .info-label {
            width: 140px;
            font-weight: bold;
            color: #495057;
            flex-shrink: 0;
        }
        .info-value {
            color: #212529;
        }
        .footer {
            background-color: #f8f9fa;
            text-align: center;
            color: #6c757d;
            font-size: 13px;
            padding: 20px;
            border-top: 1px solid #eee;
        }
        .btn-container {
            text-align: center;
            margin-top: 30px;
        }
        .btn {
            display: inline-block;
            background-color: #dc3545;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 15px;
            box-shadow: 0 2px 4px rgba(220, 53, 69, 0.2);
            transition: background-color 0.2s ease;
        }
        .btn:hover {
            background-color: #bb2d3b;
        }
        .link-text {
            margin-top: 20px;
            font-size: 12px;
            color: #6c757d;
            text-align: center;
            line-height: 1.4;
        }
        .link-text a {
            color: #0d6efd;
            text-decoration: none;
            word-break: break-all;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>⚠️ SLA Breach Alert</h1>
    </div>

    <div class="content">
        <p class="greeting">Hi <strong>{{ $user_name }}</strong>,</p>
        
        <div class="warning-text">
            <strong>Urgent Notification:</strong> The following ticket has breached its SLA Target Resolution Time and remains unresolved. Immediate action is required.
        </div>

        <div class="ticket-info">
            <h3>Ticket Details</h3>
            <div class="info-row">
                <span class="info-label">Ticket ID:</span>
                <span class="info-value">#{{ $ticket_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Title:</span>
                <span class="info-value">{{ $ticket_title }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Breach Time:</span>
                <span class="info-value" style="color: #dc3545; font-weight: bold;">
                    @if($due_at)
                        {{ \Carbon\Carbon::parse($due_at)->format('d M Y, h:i A') }}
                    @else
                        N/A
                    @endif
                </span>
            </div>
        </div>

        @php
            $ticketUrl = rtrim(env('FRONTEND_URL', config('app.url')), '/') . '/dashboard/tickets?id=' . $ticket_id;
        @endphp

        <div class="btn-container">
            <a href="{{ $ticketUrl }}" class="btn">
                View Ticket #{{ $ticket_id }}
            </a>
        </div>

        <div class="link-text">
            If the button above does not work, copy and paste this URL into your browser:<br>
            <a href="{{ $ticketUrl }}">{{ $ticketUrl }}</a>
        </div>
    </div>

    <div class="footer">
        <p>This is an automated SLA notification.<br>Thank you, McDonald's Support Team</p>
    </div>
</div>

</body>
</html>
