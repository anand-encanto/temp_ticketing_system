<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ config('app.name') }}</title>
</head>

<body style="font-family: 'Poppins', sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); border-radius: 8px; overflow: hidden;">

        <!-- Header Section -->
        <table role="presentation" width="100%" style="background-color: #27433c; padding: 24px 20px; text-align: center;">
            <tr>
                <td>
                    <h2 style="margin: 0; color: #ffffff; font-size: 22px; font-family: 'Poppins', sans-serif; letter-spacing: 1px;">{{ config('app.name') }}</h2>
                </td>
            </tr>
        </table>

        <!-- Content Section -->
        <table role="presentation" width="100%" style="padding: 30px 20px;">
            <tr>
                <td style="font-size: 16px; color: #313131; line-height: 1.6;">
                    <h1 style="font-size: 22px; color: #27433c; margin-bottom: 10px;">Welcome, {{ $user_name }}!</h1>
                    <p style="color: #555; margin-top: 0;">Your account has been created on the <strong>{{ config('app.name') }}</strong> support portal. Below are your login credentials:</p>

                    <div style="background-color: #f8f8f8; padding: 20px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #27433c;">
                        <p style="margin: 0 0 8px; color: #333;"><strong>Name:</strong> {{ $user_name }}</p>
                        <p style="margin: 0 0 8px; color: #333;"><strong>Email:</strong> {{ $user_email }}</p>
                        <p style="margin: 0 0 8px; color: #333;"><strong>Username:</strong> {{ $username }}</p>
                        <p style="margin: 0; color: #333;"><strong>Password:</strong> {{ $plain_password }}</p>
                    </div>

                    <p style="color: #e05a00; font-size: 14px; margin-bottom: 20px;">
                        ⚠️ Please log in and change your password as soon as possible to keep your account secure.
                    </p>

                    <a href="{{ $login_url }}" style="display: inline-block; background-color: #27433c; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-weight: bold; font-size: 15px; margin-bottom: 24px;">
                        Go to Login
                    </a>

                    <p style="margin-top: 24px; color: #666;">If you did not expect this email or have any questions, please contact your system administrator.</p>
                    <p style="margin: 20px 0 0; color: #313131; font-weight: bold;">Best regards,<br>{{ config('app.name') }} Team</p>
                </td>
            </tr>
        </table>

        <!-- Footer Section -->
        <table role="presentation" width="100%" style="background-color: #f4f4f4; padding: 15px; text-align: center;">
            <tr>
                <td style="font-size: 12px; color: #888;">
                    <p>© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                    <p><a href="#" style="color: #27433c; text-decoration: none;">Privacy Policy</a></p>
                </td>
            </tr>
        </table>

    </div>
</body>

</html>
