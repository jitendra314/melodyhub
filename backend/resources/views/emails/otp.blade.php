@extends('emails.layouts.app')

@section('content')
    <h3>

        {{ $title }}

    </h3>

    <p>

        Hello {{ $name }},

    </p>

    <p>

        {{ $body }}

    </p>

    <div style="
text-align:center;
margin:35px 0;
">

        <span
            style="
display:inline-block;
padding:18px 30px;
font-size:34px;
font-weight:bold;
letter-spacing:10px;
background:#f4f4f4;
border-radius:8px;
">

            {{ $otp }}

        </span>

    </div>

    <p>

        This OTP is valid for

        <strong>

            {{ config('auth.email_otp_expiry') }}

            minutes.

        </strong>

    </p>

    <p>

        If you did not request this action, you can safely ignore this email.

    </p>
@endsection
