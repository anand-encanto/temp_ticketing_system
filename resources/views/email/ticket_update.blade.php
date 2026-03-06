<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Updated</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 650px;
            background: #fff;
            margin: 40px auto;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: #0069d9;
            color: #fff;
            text-align: center;
            padding: 22px 10px;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
        }

        .content {
            padding: 25px;
        }

        .info-box {
            background: #f1f5f9;
            border-left: 5px solid #0069d9;
            padding: 15px 20px;
            border-radius: 8px;
        }

        .info-box p {
            margin: 7px 0;
            font-size: 15px;
        }

        .footer {
            background: #f3f4f6;
            padding: 15px;
            text-align: center;
            color: #555;
            font-size: 14px;
        }

        .badge {
            padding: 5px 12px;
            color: #fff;
            border-radius: 5px;
            font-size: 13px;
            text-transform: uppercase;
        }

        .badge-open {
            background: #28a745;
        }

        .badge-inprogress {
            background: #ffc107;
        }

        .badge-closed {
            background: #dc3545;
        }

        .btn {
            display: inline-block;
            background-color: #0069d9;
            color: #ffffff !important;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
        }

        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>

    <div class="container">

        <div class="header">
            <h1>Ticket #{{ $ticket_id }} Updated</h1>
        </div>

        <div class="content">

            <p>Hello,</p>
            <p>Your ticket has been updated. Below are the latest details:</p>

            <div class="info-box">
                <p><strong>Ticket ID:</strong> #{{ $ticket_id }}</p>

                <p><strong>Title:</strong> {{ $ticket_title }}</p>

                <p><strong>Updated By:</strong> {{ $updated_by }}</p>

                <p>
                    <strong>Status:</strong>
                    @php
                        $statusClass = strtolower($ticket_status);
                    @endphp

                    <span class="badge badge-{{ $statusClass }}">
                        {{ $ticket_status }}
                    </span>
                </p>

                <p><strong>Description:</strong></p>
                <p>{{ $description }}</p>
            </div>

            @php
                // $ticketUrl = config('app.frontend_url') . '/tickets/' . $ticket_id;
                $ticketUrl = rtrim(config('app.url'), '/') . '/tickets/' . $ticket_id;
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
            <p>Thank you,<br>McDonald's Ticketing Team</p>
        </div>

    </div>

</body>

</html>
