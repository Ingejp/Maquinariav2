<x-layouts.app title="Iniciar sesión">
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-sm">
            <h1 class="mb-1 text-center text-xl font-semibold text-slate-900">{{ config('app.name') }}</h1>
            <p class="mb-8 text-center text-sm text-slate-500">Inicia sesión para continuar</p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="username" class="mb-1 block text-sm font-medium text-slate-700">Usuario</label>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 text-base text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
                    >
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Contraseña</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 text-base text-slate-900 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-slate-900 px-4 py-3 text-base font-medium text-white hover:bg-slate-800"
                >
                    Entrar
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-slate-400">
                ¿Olvidaste tu contraseña? Contacta a un administrador.
            </p>
        </div>
    </div>
</x-layouts.app>
