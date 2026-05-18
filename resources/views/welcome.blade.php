<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Dashboard</title>
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
</head>
<body>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100">
        <div class="bg-white shadow rounded-lg p-8 text-center">
            <h1 class="text-4xl font-bold mb-4">Dashboard de Pedidos</h1>
            <p class="text-gray-600 mb-8">Sistema de gestión de logística para e-commerce</p>
            
            @auth
                <p class="mb-4">Bienvenido, <strong>{{ auth()->user()->name }}</strong></p>
                <a href="{{ route('dashboard.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Ir al Dashboard
                </a>
            @else
                <p class="text-gray-600 mb-8">Inicia sesión con tu cuenta de GitHub para acceder.</p>
                <a href="{{ route('auth.github.redirect') }}" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-3 px-6 rounded flex items-center justify-center gap-2 inline-block">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 0C4.477 0 0 4.484 0 10.017c0 4.425 2.865 8.18 6.839 9.49.5.092.682-.217.682-.482 0-.237-.008-.868-.013-1.703-2.782.603-3.369-1.343-3.369-1.343-.454-1.156-1.11-1.463-1.11-1.463-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.544 2.914 1.194.092-.929.351-1.544.639-1.898-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0110 4.817c.85.004 1.705.114 2.504.336 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.203 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C17.137 18.193 20 14.439 20 10.017 20 4.484 15.522 0 10 0z" clip-rule="evenodd" />
                    </svg>
                    Inicia sesión con GitHub
                </a>
            @endauth
        </div>
    </div>
</body>
</html>