<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- RFC -->
        <div>
            <label for="rfc" class="block text-sm font-bold leading-6 text-slate-700">RFC</label>
            <div class="mt-2">
                <input id="rfc" name="rfc" type="text" :value="old('rfc')" required autofocus autocomplete="rfc" 
                    class="block w-full rounded-md border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-900 sm:text-sm sm:leading-6 uppercase bg-gray-50 focus:bg-white transition" 
                    placeholder="XAXX010101000">
                <x-input-error :messages="$errors->get('rfc')" class="mt-2" />
            </div>
        </div>

        <!-- Usuario -->
        <div>
            <label for="username" class="block text-sm font-bold leading-6 text-slate-700">Usuario</label>
            <div class="mt-2">
                <input id="username" name="username" type="text" :value="old('username')" required autocomplete="username" 
                    class="block w-full rounded-md border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-900 sm:text-sm sm:leading-6 bg-gray-50 focus:bg-white transition">
                <x-input-error :messages="$errors->get('username')" class="mt-2" />
            </div>
        </div>

        <!-- Contraseña -->
        <div>
            <label for="password" class="block text-sm font-bold leading-6 text-slate-700">Contraseña</label>
            <div class="mt-2">
                <input id="password" name="password" type="password" required autocomplete="current-password" 
                    class="block w-full rounded-md border-0 py-2.5 text-slate-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-900 sm:text-sm sm:leading-6 bg-gray-50 focus:bg-white transition">
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember_me" type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-blue-900 focus:ring-blue-900">
                <label for="remember_me" class="ml-2 block text-sm text-slate-600">Recordarme</label>
            </div>

            @if (Route::has('password.request'))
                <div class="text-sm">
                    <a href="{{ route('password.request') }}" class="font-medium text-blue-900 hover:text-blue-700 hover:underline">
                        ¿Olvidó su contraseña?
                    </a>
                </div>
            @endif
        </div>

        <!-- Botón Submit (Branding Color) -->
        <div>
            <button type="submit" class="flex w-full justify-center rounded-md bg-slate-900 px-3 py-3 text-sm font-bold leading-6 text-white shadow-lg hover:bg-blue-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-900 transition duration-300 ease-in-out transform hover:-translate-y-0.5">
                Ingresar al Sistema
            </button>
        </div>
    </form>
</x-guest-layout>