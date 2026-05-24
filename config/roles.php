<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Roles del sistema (clave => etiqueta visible)
    |--------------------------------------------------------------------------
    */
    'labels' => [
        'administrador' => 'Administrador',
        'estudiante' => 'Estudiante',
        'profesor proyecto' => 'Docente',
        'coordinador' => 'Coordinación',
    ],

    /*
    |--------------------------------------------------------------------------
    | Botones del módulo "Acceso por Rol" (clave UI => rol interno)
    |--------------------------------------------------------------------------
    */
    'module_buttons' => [
        'estudiante' => ['slug' => 'estudiante', 'label' => 'Estudiante'],
        'administrador' => ['slug' => 'administrador', 'label' => 'Administrador'],
        'coordinacion' => ['slug' => 'coordinador', 'label' => 'Coordinación'],
        'docente' => ['slug' => 'profesor proyecto', 'label' => 'Docente'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alias usados en rutas/vistas legacy
    |--------------------------------------------------------------------------
    */
    'aliases' => [
        'coordinador' => ['coordinador', 'coordinador coordinacion', 'COORDINADOR_Coordinación_TEMP_PLACEHOLDER', 'coordinacion'],
        'profesor proyecto' => ['profesor proyecto', 'profesor', 'docente'],
        'administrador' => ['administrador', 'admin'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Mapeo usu_cod_rol (BD externa) => rol del sistema
    |--------------------------------------------------------------------------
    */
    'usu_cod_rol_map' => [
        1 => 'administrador',
        2 => 'coordinador',
    ],

    'session_key' => 'active_role',

    /*
    |--------------------------------------------------------------------------
    | Simula en sesión el acceso del rol elegido (menú y permisos), sin copiar BD.
    |--------------------------------------------------------------------------
    */
    'allow_free_session_roles' => env('ROLES_ALLOW_FREE_SESSION', true),

];
