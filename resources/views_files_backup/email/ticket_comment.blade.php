<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Comment on Ticket</title>
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
            background-color: #6f42c1;
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
        .comment-box {
            background-color: #f3f4f6;
            border-left: 4px solid #6f42c1;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
        }
        .comment-box p {
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
            background-color: #6f42c1;
            color: #ffffff;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #563d7c;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>{{$subject}}</h1>
    </div>

    <div class="content">
        <p>Hello,</p>
        <p>A new comment has been added to your ticket by <strong>{{ $comment_user }}</strong>.</p>

        <div class="comment-box">
            <p><strong>Ticket ID:</strong> #{{ $ticket_id }}</p>
            <p><strong>Title:</strong> {{ $ticket_title }}</p>
            <p><strong>Status:</strong> {{ $ticket_status }}</p>
            <p><strong>Comment:</strong></p>
            <p>{{ $comment }}</p>
        </div>

    </div>

    <div class="footer">
        <p>Thank you,<br>McDonald's Team</p>
    </div>
</div>

</body>
</html>
