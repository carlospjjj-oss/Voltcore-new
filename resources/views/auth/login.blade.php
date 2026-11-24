<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — VoltCore CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="h-full bg-gray-950 flex items-center justify-center p-4">

<div class="w-full max-w-sm">
    {{-- Logo --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-yellow-400 to-orange-500 mb-4 shadow-lg shadow-yellow-500/20">
            <svg class="w-8 h-8 text-gray-900" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-white font-display">VoltCore CRM</h1>
        <p class="text-gray-400 text-sm mt-1">Entre com sua conta para continuar</p>
    </div>

    {{-- Card --}}
    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-8 shadow-xl">
        @if($errors->any())
        <div class="mb-5 flex items-center gap-2 bg-red-900/30 border border-red-800 text-red-400 px-4 py-3 rounded-xl text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">E-mail</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full px-4 py-2.5 rounded-xl bg-gray-800 border border-gray-700 text-white placeholder-gray-500
                              focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition text-sm"
                       placeholder="seu@email.com.br">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Senha</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-2.5 rounded-xl bg-gray-800 border border-gray-700 text-white placeholder-gray-500
                              focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition text-sm"
                       placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-gray-600 bg-gray-800 text-yellow-500 focus:ring-yellow-500">
                    Lembrar de mim
                </label>
            </div>

            <button type="submit"
                    class="w-full py-2.5 px-4 rounded-xl bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-semibold text-sm transition-colors shadow-lg shadow-yellow-500/20">
                Entrar
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-gray-600 mt-6">VoltCore CRM © {{ date('Y') }} — Proton Mobilidade</p>
</div>

</body>
</html>
