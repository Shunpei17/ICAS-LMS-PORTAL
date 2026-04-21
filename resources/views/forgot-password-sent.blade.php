<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Check Your Email | ICAS LMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen p-4 bg-cover bg-center bg-no-repeat" style="background-image: url('{{ asset('images/green.png') }}');">
<div class="text-center w-full max-w-sm">
    <div class="text-center mb-8">
        <img src="{{ asset('images/icas-logo.png') }}" alt="ICAS Philippines Logo" class="mx-auto h-24 w-auto object-contain">
        <p class="mt-2 text-[#388e3c] text-sm font-bold">CHECK YOUR EMAIL</p>
    </div>

    <div class="bg-[#52af59] p-8 rounded-[2rem] shadow-xl border border-white/20 text-white">
        <h1 class="text-xl font-bold text-center">Password Reset Sent</h1>

        <p class="text-sm text-white/90 mt-3 leading-relaxed">
            If an account exists for the email you entered, we have sent password reset instructions.
        </p>

        @if($email !== '')
            <p class="mt-4 rounded-xl bg-white/15 px-4 py-3 text-xs text-white/95 break-all">
                Requested for: {{ $email }}
            </p>
        @endif

        <p class="mt-4 text-xs text-white/80 leading-relaxed">
            Please check your inbox and spam folder. The link will expire soon for security.
        </p>

        <div class="mt-6 space-y-3">
            <a href="{{ route('password.request') }}" class="block w-full rounded-xl bg-white text-[#2e7d32] py-3 text-sm font-bold hover:bg-green-50 transition">
                Send Another Link
            </a>
            <a href="{{ route('login') }}" class="block w-full rounded-xl border border-white/40 py-3 text-sm font-semibold text-white hover:bg-white/10 transition">
                Back to Sign In
            </a>
        </div>
    </div>
</div>
</body>
</html>
