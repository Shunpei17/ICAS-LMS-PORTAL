<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password | ICAS LMS</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen p-4 bg-cover bg-center bg-no-repeat" style="background-image: url('{{ asset('images/green.png') }}');">
<div class="text-center w-full max-w-sm">
    <div class="text-center mb-8">
        <img src="{{ asset('images/icas-logo.png') }}" alt="ICAS Philippines Logo" class="mx-auto h-24 w-auto object-contain">
        <p class="mt-2 text-[#388e3c] text-sm font-bold">CREATE A NEW PASSWORD</p>
    </div>

    <div class="bg-[#52af59] p-8 rounded-[2rem] shadow-xl border border-white/20 text-white">
        <h1 class="text-xl font-bold text-center">Reset Password</h1>

        @if($errors->any())
            <div class="bg-red-50 text-red-600 text-xs p-3 rounded-xl mt-5 text-left border border-red-100">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-white/80">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    placeholder="email@school.edu"
                    class="w-full p-3 pl-10 bg-white/20 border border-white/30 rounded-xl focus:ring-2 focus:ring-white outline-none transition-all text-sm text-white placeholder-white/70"
                    required
                >
            </div>

            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-white/80">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <input
                    type="password"
                    name="password"
                    placeholder="New password"
                    class="w-full p-3 pl-10 bg-white/20 border border-white/30 rounded-xl focus:ring-2 focus:ring-white outline-none transition-all text-sm text-white placeholder-white/70"
                    required
                >
            </div>

            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-white/80">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <input
                    type="password"
                    name="password_confirmation"
                    placeholder="Confirm new password"
                    class="w-full p-3 pl-10 bg-white/20 border border-white/30 rounded-xl focus:ring-2 focus:ring-white outline-none transition-all text-sm text-white placeholder-white/70"
                    required
                >
            </div>

            <button type="submit" class="w-full bg-[#388e3c] py-3 rounded-xl font-bold text-white hover:bg-[#2e7d32] transition-all shadow-lg shadow-[#388e3c]/20">
                Reset Password
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-white/85">
            <a href="{{ route('login') }}" class="font-semibold text-white hover:text-white/75 transition">Back to Sign In</a>
        </p>
    </div>
</div>
</body>
</html>
