<?php

use App\Http\Controllers\MagicLoginController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::view('/repositorio', 'repositorio')->name('repositorio');

Route::get('/magic-login', [MagicLoginController::class, 'login'])->name('magic-login');

Route::get('/login', function (\Illuminate\Http\Request $request) {
    if ($request->has('payload')) {
        return app(MagicLoginController::class)->login($request);
    }
    if ($request->has('token')) {
        return app(MagicLoginController::class)->login($request);
    }

    return view('auth.access-info');
})->name('login');

Route::middleware('auth')->group(function () {
    Route::view('/acceso-por-rol', 'acceso_rol.index')->name('acceso-rol.index');
    Route::redirect('/sesion/rol', '/acceso-por-rol');
});

Route::middleware(['auth', 'active.role'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/configuracion', 'configuracion.index')->name('configuracion');

    Route::middleware('role:administrador')->group(function () {
        Route::view('/configuracion/coordinadores', 'configuracion.coordinadores')->name('coordinadores.index');
    });

    Route::middleware('role:administrador,estudiante')->group(function () {
        Route::view('/lineas-investigacion', 'lineas.index')->name('lineas-investigacion');
        Route::view('/tipos-investigacion', 'tipo_investigacion.index')->name('tipos-investigacion');
        Route::view('/metodologia-investigacion', 'metodologia_investigacion.index')->name('metodologia-investigacion');
        Route::view('/tipos-publicacion', 'tipo_publicacion.index')->name('tipos-publicacion');
    });

    Route::redirect('/lapsos-academicos', '/dashboard')->name('lapsos-academicos');

    Route::view('/proyectos', 'proyectos.index')->name('proyectos.index');
    Route::view('/proyectos/buscar', 'proyectos.buscar')->name('proyectos.buscar');
    Route::view('/comunidades', 'comunidades.index')->name('comunidades.index');
    Route::view('/grupos-proyecto', 'grupos_proyecto.index')->name('grupos-proyecto.index');

    Route::middleware('role:administrador,estudiante,coordinador,profesor proyecto')->group(function () {
        Route::view('/proyectos/gestion', 'proyectos.index')->name('proyectos.gestion');
    });

    Route::get('/proyectos/crear', function () {
        return redirect()->route('proyectos.gestion', request()->query());
    })->middleware('role:administrador,estudiante,coordinador,profesor proyecto')->name('proyectos.crear');

    Route::get('/validaciones', function () {
        return redirect('/proyectos/gestion?tab=validar');
    })->middleware('role:administrador,coordinador,profesor proyecto')->name('validaciones.index');

    Route::middleware('role:administrador,coordinador')->group(function () {
        Route::view('/configuracion/profesores-proyecto', 'profesores_proyecto.index')->name('profesores-proyecto.index');
        Route::view('/configuracion/componentes', 'componentes.index')->name('componentes.index');
    });
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');
