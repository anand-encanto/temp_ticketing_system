<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>New Ticket Created</title>
</head>

<body style="font-family: Arial, sans-serif; background: #f7f7f7; padding: 30px;">

    <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 25px; border-radius: 8px;">

        <h2 style="color: #333;">🚨 New Ticket Created</h2>

        <p style="font-size:16px; color:#444;">
            A new ticket has been created in the system.
        </p>

        <table style="width:100%; margin-top: 15px; border-collapse: collapse;">
            <tr>
                <td style="padding:8px; font-weight:bold;">Ticket ID:</td>
                <td style="padding:8px;">{{ $ticket_id }}</td>
            </tr>

            <tr>
                <td style="padding:8px; font-weight:bold;">Title:</td>
                <td style="padding:8px;">{{ $ticket_title }}</td>
            </tr>

            <tr>
                <td style="padding:8px; font-weight:bold;">Status:</td>
                <td style="padding:8px;">{{ $ticket_status }}</td>
            </tr>

            <tr>
                <td style="padding:8px; font-weight:bold;">Description:</td>
                <td style="padding:8px;">{{ $ticket_description }}</td>
            </tr>

            <tr>
                <td style="padding:8px; font-weight:bold;">Submitted By:</td>
                <td style="padding:8px;">{{ $submitter_name }}</td>
            </tr>
        </table>

        <br>

        @php
        // $ticketUrl = config('app.frontend_url') . '/tickets/' . $ticket_id;
        $ticketUrl = rtrim(env('FRONTEND_URL', config('app.url')), '/') . '/tickets/' . $ticket_id;
        @endphp

        <a href="{{ $ticketUrl }}"
            style="display:inline-block;
                  background:#007bff;
                  color:#ffffff !important;
                  padding:10px 18px;
                  text-decoration:none;
                  border-radius:6px;
                  font-size:14px;">
            View Ticket #{{ $ticket_id }}
        </a>

        <p style="margin-top:15px;font-size:13px;color:#555;">
            If the button doesn’t work, copy and paste this link into your browser:<br>
            <a href="{{ $ticketUrl }}">{{ $ticketUrl }}</a>
        </p>

        <p style="font-size: 14px; color:#777;">Thank you.</p>

    </div>

</body>

</html>