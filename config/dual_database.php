<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Conexión MySQL del módulo repositorio (proyectos, comunidades, etc.)
    |--------------------------------------------------------------------------
    */
    'repositorio_connection' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Tablas que viven en intranet (PostgreSQL) / simulación — datos académicos
    |--------------------------------------------------------------------------
    */
    'intranet_tables' => [
        'usuario',
        'persona',
        'estudiante',
        'programa',
        'seccion',
        'seccion_unidad_docente',
        'inscripcion',
        'lapso_academico',
        'malla',
        'trayecto',
        'semestre',
        'unidad_curricular',
        'rol',
        'grupo_proyecto_estudiante',
    ],

    /*
    |--------------------------------------------------------------------------
    | Tablas solo en MySQL repositorio — gestión del sistema de proyectos
    |--------------------------------------------------------------------------
    */
    'repositorio_tables' => [
        'proyectos',
        'proyecto_documentos',
        'comunidades',
        'comunidad_estudiante',
        'coordinaciones',
        'linea_investigacions',
        'metodologia_investigacions',
        'tipo_investigacions',
        'tipo_publicacions',
        'equipos',
        'equipo_estudiante',
        'auditorias',
        'roles',
        'direcciones',
    ],

    /*
    |--------------------------------------------------------------------------
    | Copia local en repositorio para doble lectura si intranet no responde
    |--------------------------------------------------------------------------
    */
    'repositorio_mirror_tables' => [
        'lapso_academico',
    ],

    'local_aliases' => [
        'lapso_academico' => 'lapso_academico',
    ],

];
