<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Repositorio Institucional - UPTP Juan de Jesús Montilla</title>
    <link rel="icon" type="image/png" href="{{ asset('imagenes/uptp-logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Outfit', sans-serif; }
        .glass { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <!-- Hero Section -->
    <div class="relative min-h-[600px] flex flex-col overflow-hidden bg-slate-900">
        <!-- Abstract Background -->
        <div class="absolute inset-0 z-0">
            <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-blue-600/20 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-emerald-600/10 rounded-full blur-[120px]"></div>
        </div>

        <!-- Navbar -->
        <nav class="relative z-10 container mx-auto px-6 h-24 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                    <i data-lucide="library" class="w-6 h-6 text-white"></i>
                </div>
                <span class="text-xl font-bold text-white tracking-tight">Repositorio <span class="text-blue-400">UPTP</span></span>
            </div>
            <div class="flex items-center gap-6">
                <a href="/repositorio" class="text-slate-300 hover:text-white transition-colors text-sm font-medium">Repositorio</a>
                <a href="/repositorio?filterCoordinacion=1" class="text-slate-300 hover:text-white transition-colors text-sm font-medium">Coordinaciones</a>
                @auth
                    <a href="/dashboard" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-full font-bold text-sm transition-all shadow-lg shadow-blue-500/20">Ir al Panel</a>
                @else
                    <a href="/login" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-full font-bold text-sm transition-all shadow-lg shadow-blue-500/20">Iniciar Sesión</a>
                @endauth
            </div>
        </nav>

        <!-- Hero Content -->
        <div class="relative z-10 container mx-auto px-6 pt-16 text-center max-w-4xl flex-1 flex flex-col justify-center">
            <h1 class="text-4xl md:text-6xl font-extrabold text-white leading-tight mb-8">
                Preservando la Excelencia Académica de la <span class="text-blue-500">UPTP Juan de Jesús Montilla</span>
            </h1>
            <p class="text-slate-400 text-lg md:text-xl mb-12 max-w-2xl mx-auto">
                Accede a la producción intelectual, proyectos de investigación y pasantías validadas de nuestra comunidad académica.
            </p>

            <!-- Search Bar -->
            <form action="/repositorio" method="GET" class="relative max-w-2xl mx-auto w-full group">
                <div class="absolute inset-y-0 left-0 pl-6 flex items-center text-slate-500 group-focus-within:text-blue-500 transition-colors">
                    <i data-lucide="search" class="w-6 h-6"></i>
                </div>
                <input type="text" name="search" placeholder="Buscar por título, autor o Coordinación..." 
                    class="w-full pl-16 pr-6 py-5 bg-white/10 hover:bg-white/15 focus:bg-white text-white focus:text-slate-900 border border-white/10 focus:border-white rounded-2xl outline-none transition-all text-lg shadow-2xl backdrop-blur-md">
                <button type="submit" class="absolute top-2.5 right-2.5 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-all">Buscar</button>
            </form>
        </div>

        <!-- Wave Divider -->
        <div class="absolute bottom-0 left-0 w-full overflow-hidden leading-none">
            <svg class="relative block w-full h-[60px]" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" fill="#f8fafc"></path>
            </svg>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="container mx-auto px-6 py-12 -mt-10 relative z-20">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="glass p-8 rounded-3xl border border-white/50 shadow-xl text-center">
                <div class="text-3xl font-bold text-blue-600 mb-1">150+</div>
                <div class="text-sm font-semibold text-slate-500 uppercase tracking-widest">Proyectos Informática</div>
            </div>
            <div class="glass p-8 rounded-3xl border border-white/50 shadow-xl text-center">
                <div class="text-3xl font-bold text-emerald-600 mb-1">80+</div>
                <div class="text-sm font-semibold text-slate-500 uppercase tracking-widest">Proyectos Agro</div>
            </div>
            <div class="glass p-8 rounded-3xl border border-white/50 shadow-xl text-center">
                <div class="text-3xl font-bold text-blue-600 mb-1">12</div>
                <div class="text-sm font-semibold text-slate-500 uppercase tracking-widest">Coordinaciones Activos</div>
            </div>
            <div class="glass p-8 rounded-3xl border border-white/50 shadow-xl text-center">
                <div class="text-3xl font-bold text-indigo-600 mb-1">1.2k</div>
                <div class="text-sm font-semibold text-slate-500 uppercase tracking-widest">Descargas Mes</div>
            </div>
        </div>
    </div>

    @include('welcome-content')

    <footer class="bg-slate-900 py-12 mt-20">
        <div class="container mx-auto px-6 text-center">
            <div class="flex items-center justify-center gap-3 mb-6">
                <div class="h-8 w-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <i data-lucide="library" class="w-5 h-5 text-white"></i>
                </div>
                <span class="text-lg font-bold text-white tracking-tight">Repositorio UPTP</span>
            </div>
            <p class="text-slate-500 text-sm">© 2026 Universidad Politécnica Territorial Juan de Jesús Montilla. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
