<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'E&I Acceso') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full font-sans antialiased text-slate-900">
        <div class="flex min-h-full">
            
            <!-- Columna Izquierda: Formulario -->
            <div class="flex flex-1 flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24 bg-white shadow-xl z-10">
                <div class="mx-auto w-full max-w-sm lg:w-96">
                    <div class="mb-10 text-center lg:text-left">
                        <a href="/" class="inline-block">
                            <!-- LOGO E&I -->
                            <img src="{{ asset('images/logo-ei.png') }}" alt="E&I Logo" class="h-20 w-auto">
                        </a>
                        <h2 class="mt-8 text-2xl font-bold tracking-tight text-slate-900">Portal de Servicios</h2>
                        <p class="mt-2 text-sm text-slate-500">
                            Sistema de Manifestación de Valor Electrónica
                        </p>
                    </div>

                    <div class="mt-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>

            <!-- Columna Derecha: Imagen Corporativa -->
            <div class="relative hidden w-0 flex-1 lg:block">
                <!-- Imagen de fondo profesional (Puerto/Logística) -->
                <img class="absolute inset-0 h-full w-full object-cover" src="https://images.unsplash.com/photo-1578575437130-527eed3abbec?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80" alt="Logística Internacional">
                <!-- Overlay con el color AZUL oscuro del logo (#0F172A / slate-900 con opacidad) -->
                <div class="absolute inset-0 bg-slate-900 mix-blend-multiply opacity-80"></div>
                <div class="absolute inset-0 flex flex-col justify-center items-center text-white p-12 text-center">
                    <div class="max-w-2xl">
                        <h3 class="text-4xl font-bold mb-6">Comercio Exterior, Logística y Tecnología</h3>
                        <p class="text-lg text-slate-200">Soluciones integrales para la gestión aduanera eficiente.</p>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>