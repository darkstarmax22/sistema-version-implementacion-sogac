<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\MagicLoginController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return redirect()->route('login');
});

Route::get('/repositorio', function () {
    return view('repositorio');
})->name('repositorio');

// Login: si llega con ?payload= procesa el enlace, si no muestra página informativa
Route::get('/login', function (\Illuminate\Http\Request $request) {
    if ($request->has('payload')) {
        return app(MagicLoginController::class)->login($request);
    }
    return response('<html><body style="font-family:Verdana;text-align:center;padding:80px;background:#f5f5f5;">
        <h1 style="color:#333;">Acceso al Sistema</h1>
        <p style="color:#666;font-size:16px;">Para acceder, solicite su enlace de acceso al administrador del sistema.</p>
        <p style="color:#999;font-size:13px;margin-top:20px;">El enlace se genera desde la terminal con: <code>php artisan app:generate-login-link</code></p>
    </body></html>');
})->name('login');

Route::middleware(['auth'])->group(function () {
    Route::get('/acceso-por-rol', function () {
        return view('acceso_rol.index');
    })->name('acceso-rol.index');

    Route::redirect('/sesion/rol', '/acceso-por-rol');
});

Route::middleware(['auth', 'active.role'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Módulos de Gestión Académica (Solo Administrador)
    Route::middleware(['role:administrador'])->group(function() {
        Route::get('/coordinaciones', function () {
            return view('coordinaciones.index');
        })->name('coordinaciones');

        Route::get('/lapsos-academicos', function () {
            return view('lapso_academico.index');
        })->name('lapsos-academicos');
    });

    // Módulos Compartidos (Estudiantes y Administrador)
    Route::middleware(['role:administrador,estudiante'])->group(function() {
        Route::get('/lineas-investigacion', function () {
            return view('lineas.index');
        })->name('lineas-investigacion');

        Route::get('/tipos-investigacion', function () {
            return view('tipo_investigacion.index');
        })->name('tipos-investigacion');

        Route::get('/metodologia-investigacion', function () {
            return view('metodologia_investigacion.index');
        })->name('metodologia-investigacion');

        Route::get('/tipos-publicacion', function () {
            return view('tipo_publicacion.index');
        })->name('tipos-publicacion');
    });







    // Módulos de Proyectos
    Route::get('/proyectos', function () {
        return view('proyectos.index');
    })->name('proyectos.index');

    // Módulo Metodologías
    Route::get('/metodologias', function () {
        return view('metodologias.index');
    })->name('metodologias.index');

    // Módulo Comunidades
    Route::get('/comunidades', function () {
        return view('comunidades.index');
    })->name('comunidades.index');

    // Módulo Grupos de Proyecto
    Route::get('/grupos-proyecto', function () {
        return view('grupos_proyecto.index');
    })->name('grupos-proyecto.index');

    Route::get('/proyectos/buscar', function () {
        return view('proyectos.buscar');
    })->name('proyectos.buscar');

    Route::middleware(['role:administrador,profesor proyecto'])->group(function() {
        Route::get('/validaciones', function () {
            return view('validaciones.index');
        })->name('validaciones.index');
    });

    Route::middleware(['role:administrador,estudiante'])->group(function() {
        Route::get('/proyectos/crear', function () {
            return view('proyectos.index'); 
        })->name('proyectos.crear');
    });


    // Módulos de Validación (Docentes y Admin)
    // ELIMINADO: Módulo de Validaciones (Dependencia de Proyectos)


    // Módulos de Sistema (Administrador y COORDINADOR_Coordinación_TITLE_TEMP_PLACEHOLDER para Profesores Proy.)
    Route::middleware(['role:administrador,COORDINADOR_Coordinación_TEMP_PLACEHOLDER'])->group(function() {
        Route::get('/configuracion/profesores-proyecto', function () {
            return view('profesores_proyecto.index');
        })->name('profesores-proyecto.index');

        Route::get('/configuracion/componentes', function () {
            return view('componentes.index');
        })->name('componentes.index');
    });

    Route::get('/configuracion', function () {
        return view('configuracion.index');
    })->name('configuracion');

    // Módulos de Sistema (Solo Administrador)
    Route::middleware(['role:administrador'])->group(function() {
        Route::get('/auditoria', function () {
            return view('dashboard'); // Placeholder
        })->name('auditoria');

        Route::get('/configuracion/coordinadores', function () {
            return view('configuracion.coordinadores');
        })->name('coordinadores.index');
    });

});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->middleware('auth')->name('logout');
