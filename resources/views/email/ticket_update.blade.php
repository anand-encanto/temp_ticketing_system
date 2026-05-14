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
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
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
        .badge-open { background: #28a745; }
        .badge-inprogress { background: #ffc107; }
        .badge-closed { background: #dc3545; }
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

    </div>

    <div class="footer">
        <p>Thank you,<br>McDonald's Ticketing Team</p>
    </div>

</div>

</body>
</html>
