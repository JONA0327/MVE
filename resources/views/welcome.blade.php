<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>E&I - Manifestación de Valor</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased bg-white font-sans text-slate-800">
        
        <!-- Navbar -->
        <div class="bg-white border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex items-center">
                        <img src="{{ asset('images/logo-ei.png') }}" alt="E&I Logo" class="h-12 w-auto">
                    </div>
                    <div>
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/dashboard') }}" class="text-sm font-bold text-slate-700 hover:text-blue-900">Ir al Dashboard &rarr;</a>
                            @else
                                <a href="{{ route('login') }}" class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-full text-white bg-slate-900 hover:bg-blue-900 shadow-md transition">
                                    Iniciar Sesión
                                </a>
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Hero Section -->
        <main>
            <div class="relative isolate overflow-hidden bg-white">
                <div class="mx-auto max-w-7xl px-6 pb-24 pt-10 sm:pb-32 lg:flex lg:px-8 lg:py-24">
                    <div class="mx-auto max-w-2xl lg:mx-0 lg:max-w-xl lg:flex-shrink-0 lg:pt-8">
                        <div class="mt-24 sm:mt-32 lg:mt-16">
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-800 ring-1 ring-inset ring-blue-700/10">Sistema 2025</span>
                        </div>
                        <h1 class="mt-10 text-4xl font-bold tracking-tight text-slate-900 sm:text-6xl">
                            Manifestación de Valor <span class="text-blue-800">Electrónica</span>
                        </h1>
                        <p class="mt-6 text-lg leading-8 text-slate-600">
                            Plataforma oficial de E&I para la gestión, cálculo y firmado digital de documentos de valor aduanal.
                        </p>
                        <div class="mt-10 flex items-center gap-x-6">
                            <!-- Botón con color corporativo principal (Slate-900) -->
                            <a href="{{ route('login') }}" class="rounded-md bg-slate-900 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-900 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-900 transition duration-300">
                                Acceder al Portal
                            </a>
                            <a href="#" class="text-sm font-semibold leading-6 text-slate-900">Soporte Técnico <span aria-hidden="true">→</span></a>
                        </div>
                    </div>
                    <!-- Ilustración dashboard -->
                    <div class="mx-auto mt-16 flex max-w-2xl sm:mt-24 lg:ml-10 lg:mt-0 lg:mr-0 lg:max-w-none lg:flex-none xl:ml-32">
                        <div class="max-w-3xl flex-none sm:max-w-5xl lg:max-w-none">
                            <div class="-m-2 rounded-xl bg-gray-900/5 p-2 ring-1 ring-inset ring-gray-900/10 lg:-m-4 lg:rounded-2xl lg:p-4">
                                <img src="https://tailwindui.com/img/component-images/project-app-screenshot.png" alt="App screenshot" width="2432" height="1442" class="w-[76rem] rounded-md shadow-2xl ring-1 ring-gray-900/10 grayscale opacity-90">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </body>
</html>