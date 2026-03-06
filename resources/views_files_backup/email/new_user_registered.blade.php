<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>

<body style="font-family: 'Poppins', sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); border-radius: 8px; overflow: hidden;">

        <!-- Header Section -->
        <table role="presentation" width="100%" style="background-color: #27433c; padding: 20px; text-align: center;">
            <tr>
                <td>
                    <img src="{{ url('public/images/Logo.png') }}" alt="Logo" style="width: 180px; margin: 0 auto;">
                </td>
            </tr>
        </table>

        <!-- Content Section -->
        <table role="presentation" width="100%" style="padding: 30px 20px;">
            <tr>
                <td style="font-size: 18px; color: #313131; line-height: 1.6;">
                    <h1 style="font-size: 24px; color: #27433c; margin-bottom: 20px;">{{ $title }}</h1>
                    
                    <div style="background-color: #f8f8f8; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <p style="margin: 0; color: #333;"><strong>Name:</strong> {{ $name }}</p>
                        <p style="margin: 0; color: #333;"><strong>Email:</strong> {{ $user_email }}</p>
                        <p style="margin: 0; color: #333;"><strong>Phone Number:</strong> {{ $phone_number }}</p>
                        <p style="margin: 0; color: #333;"><strong>Type:</strong> {{ $user_type }}</p>
                    </div>

                    <p style="margin-top: 30px; color: #666;">Thank you for your attention. If you have any questions or need further assistance, feel free to reach out to us.</p>
                    <p style="margin: 20px 0 0; color: #313131; font-weight: bold;">Best regards,<br>Team LAIZA</p>
                </td>
            </tr>
        </table>

        <!-- Footer Section -->
        <table role="presentation" width="100%" style="background-color: #f4f4f4; padding: 15px; text-align: center;">
            <tr>
                <td style="font-size: 12px; color: #888;">
                    <p>© 2024 LAIZA. All rights reserved.</p>
                    <p><a href="#" style="color: #27433c; text-decoration: none;">Unsubscribe</a> | <a href="#" style="color: #27433c; text-decoration: none;">Privacy Policy</a></p>
                </td>
            </tr>
        </table>

    </div>
</body>

</html>
