<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
</head>

<body
    style="
        margin:0;
        padding:40px;
        background:#f4f4f4;
        font-family:Arial, Helvetica, sans-serif;
    ">

    <table width="100%" cellpadding="0" cellspacing="0">

        <tr>

            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0"
                    style="
        background:#ffffff;
        border-radius:8px;
        padding:40px;
    ">

                    <tr>

                        <td align="center">

                            <h2>

                                {{ config('app.name') }}

                            </h2>

                        </td>

                    </tr>

                    <tr>

                        <td style="padding-top:30px;">

                            @yield('content')

                        </td>

                    </tr>

                    <tr>

                        <td style="
padding-top:40px;
font-size:13px;
color:#777;
">

                            Regards,<br>

                            <strong>

                                {{ config('app.name') }}

                            </strong>

                        </td>

                    </tr>

                </table>

            </td>

        </tr>

    </table>

</body>

</html>
