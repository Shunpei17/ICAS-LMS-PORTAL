<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Reset Password</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f0f4f1;
            font-family: Arial, Helvetica, sans-serif;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        table {
            border-spacing: 0;
            border-collapse: collapse;
        }

        img {
            border: 0;
            max-width: 100%;
            line-height: 100%;
            vertical-align: middle;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f0f4f1;
            padding: 20px 12px;
        }

        .container {
            width: 100%;
            max-width: 600px;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
        }

        .content-padding {
            padding: 30px 34px;
        }

        .button {
            display: inline-block;
            padding: 14px 22px;
            background-color: #2e7d32;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: bold;
        }

        .muted {
            color: #667085;
            font-size: 14px;
            line-height: 1.6;
        }

        .headline {
            color: #0f172a;
            font-size: 24px;
            line-height: 1.3;
            margin: 0;
        }

        .greeting {
            color: #0f172a;
            font-size: 16px;
            line-height: 1.6;
            margin: 0;
        }

        .url-break {
            word-break: break-all;
        }

        @media only screen and (max-width: 620px) {
            .content-padding {
                padding: 24px 20px !important;
            }

            .headline {
                font-size: 21px !important;
            }

            .button-wrap {
                width: 100% !important;
            }

            .button {
                display: block !important;
                width: 100% !important;
                text-align: center !important;
                box-sizing: border-box !important;
            }
        }
    </style>
</head>
<body>
    <center class="wrapper">
        <table role="presentation" class="container" width="600" align="center">
            <tr>
                <td style="background-color: #2f9e44; padding: 18px 34px;">
                    <p style="margin: 0; color: #ffffff; font-size: 15px; font-weight: bold; letter-spacing: 0.04em;">
                        {{ strtoupper($appName) }}
                    </p>
                </td>
            </tr>
            <tr>
                <td class="content-padding">
                    <p class="greeting">Hi {{ $userName }},</p>
                    <p style="margin: 14px 0 0;" class="headline">Reset your password</p>

                    <p class="muted" style="margin: 16px 0 0;">
                        We received a request to reset the password for your account. Click the button below to set a new password.
                    </p>

                    <table role="presentation" width="100%" style="margin-top: 24px;">
                        <tr>
                            <td class="button-wrap" align="left">
                                <a href="{{ $resetUrl }}" class="button">Reset Password</a>
                            </td>
                        </tr>
                    </table>

                    <p class="muted" style="margin: 22px 0 0;">
                        This link expires in {{ $expiresInMinutes }} minutes. If you did not request a reset, you can safely ignore this email.
                    </p>

                    <p class="muted" style="margin: 22px 0 0;">
                        If the button does not work in your email app, copy and paste this URL into your browser:
                    </p>

                    <p class="muted url-break" style="margin: 8px 0 0; color: #2e7d32;">
                        {{ $resetUrl }}
                    </p>
                </td>
            </tr>
            <tr>
                <td style="padding: 18px 34px; background-color: #f8fafc; border-top: 1px solid #e2e8f0;">
                    <p class="muted" style="margin: 0; font-size: 12px; line-height: 1.5;">
                        Sent by {{ $appName }}
                    </p>
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
