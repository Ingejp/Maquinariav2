<x-layouts.app title="Iniciar sesión" :nav="false">
    <div class="flex min-h-screen items-center justify-center bg-surface-2 px-4 py-12">
        <div class="w-full max-w-sm rounded-xl border border-border bg-surface p-8 shadow-sm">
            <h1 class="mb-1 text-center font-display text-2xl font-semibold text-text">{{ config('app.name') }}</h1>
            <p class="mb-8 text-center text-sm text-text-muted">Inicia sesión para continuar</p>

            @if ($errors->any())
                <div class="mb-4 rounded-md border border-critical/30 bg-critical-soft px-4 py-3 text-sm text-critical">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="username" class="mb-1 block text-sm font-medium text-text">Usuario</label>
                    <input
                        id="username"
                        name="username"
                        type="text"
                        value="{{ old('username') }}"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full rounded-md border border-border px-4 py-3 text-base text-text focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                    >
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-text">Contraseña</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="w-full rounded-md border border-border px-4 py-3 text-base text-text focus:border-accent focus:outline-none focus:ring-1 focus:ring-accent"
                    >
                </div>

                <button
                    type="submit"
                    class="w-full rounded-md bg-accent px-4 py-3 text-base font-medium text-on-accent hover:bg-accent-strong"
                >
                    Entrar
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-text-muted">
                ¿Olvidaste tu contraseña? Contacta a un administrador.
            </p>
        </div>
    </div>
</x-layouts.app>
