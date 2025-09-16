@extends('emails.layout')

@section('content')
<h2>Reset Your Password</h2>

<p>Hello {{ $user->name }},</p>

<p>You are receiving this email because we received a password reset request for your account.</p>

<div style="text-align: center;">
    <a href="{{ $url }}" class="button">Reset Password</a>
</div>

<p>If you're unable to click the button, copy and paste the following URL into your web browser:</p>
<p style="word-break: break-all; color: #6b7280; font-size: 12px;">{{ $url }}</p>

<div class="alert">
    <strong>Security Notice:</strong> This password reset link will expire in 60 minutes for your security.
</div>

<p>If you did not request a password reset, no further action is required.</p>

<p>Best regards,<br>The Finder Team</p>
@endsection
