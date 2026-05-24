<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tablas de la BD externa que se replican en simulación
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'usuario',
        'persona',
        'estudiante',
        'programa',
        'seccion_unidad_docente',
        'grupo_proyecto_estudiante',
        'lapso_academico',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tablas filtradas por cédula al exportar contexto de usuario
    |--------------------------------------------------------------------------
    */
    'user_scoped' => [
        'usuario' => 'usu_cedula',
        'persona' => 'per_cedula',
        'estudiante' => 'est_cedula',
        'seccion_unidad_docente' => 'sud_ced_docente',
        'grupo_proyecto_estudiante' => 'gpe_ced_estudiante',
    ],

];
