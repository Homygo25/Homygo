@extends('emails.layout')

@section('content')
<h2>Welcome to Finder!</h2>

<p>Hello {{ $user->name }},</p>

<p>Thank you for registering with Finder! To complete your registration and verify your email address, please click the button below:</p>

<div style="text-align: center;">
    <a href="{{ $url }}" class="button">Verify Email Address</a>
</div>

<p>If you're unable to click the button, copy and paste the following URL into your web browser:</p>
<p style="word-break: break-all; color: #6b7280; font-size: 12px;">{{ $url }}</p>

<div class="alert">
    <strong>Security Notice:</strong> This verification link will expire in 60 minutes for your security.
</div>

<p>If you did not create an account with Finder, no further action is required.</p>

<p>Best regards,<br>The Finder Team</p>
@endsection
