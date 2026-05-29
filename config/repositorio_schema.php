<?php

/**
 * Mapeo entre nombres usados en el código Laravel y columnas reales en MySQL repositorio.
 */
return [

    'proyectos' => [
        'primary_key' => 'pry_codigo',
        'columns' => [
            'id' => 'pry_codigo',
            'titulo' => 'pry_titulo',
            'resumen' => 'pry_resumen',
            'fecha_subida' => 'pry_fecha_subida',
            'asignacion_ct' => 'pry_asignacion_ct',
            'calificacion' => 'pry_calificacion',
            'fecha_aprobacion' => 'pry_fecha_aprobacion',
            'archivo_path' => 'pry_archivo_path',
            'documentos' => 'pry_documentos',
            'linea_investigacion_id' => 'lin_codigo',
            'metodologia_id' => 'mei_codigo',
            'tipo_publicacion_id' => 'tpu_codigo',
            'tipo_investigacion_id' => 'tin_codigo',
            'estado_logico' => 'pry_estado_logico',
            'estado_validacion' => 'pry_estado_',
            'motivo_rechazo' => 'pry_motivo_rechazo',
            'comunidad_id' => 'com_codigo',
            'equipo_ref' => 'pry_direccion_logica',
        ],
        'values' => [
            'estado_validacion' => [
                'aprobado' => 'Aprobado',
                'pendiente' => 'Pendiente',
                'rechazado' => 'Rechazado',
            ],
        ],
    ],

    'comunidades' => [
        'primary_key' => 'com_codigo',
        'columns' => [
            'id' => 'com_codigo',
            'nombre' => 'com_nombre',
            'rif' => 'com_rif',
            'correo' => 'com_correo',
            'numero_telefono' => 'com_numero_telefono',
            'direccion' => 'com_direccion',
        ],
    ],

    'linea_investigacions' => [
        'primary_key' => 'lin_codigo',
        'columns' => [
            'id' => 'lin_codigo',
            'nombre_investigacion' => 'lin_nombre_investigacion',
            'descripcion' => 'lin_descripcion',
            'area_de_investigacion' => 'lin_area_de_investigacion',
            'coordinacion_id' => 'coord_codigo',
            'activo' => 'lin_estado',
        ],
        'values' => [
            'activo' => [
                true => 'Activo',
                false => 'Inactivo',
                1 => 'Activo',
                0 => 'Inactivo',
            ],
        ],
    ],

    'tipo_publicacions' => [
        'primary_key' => 'tpu_codigo',
        'columns' => [
            'id' => 'tpu_codigo',
            'nombre' => 'tpu_nombre',
            'mencion_honorifica' => 'tpu_mencion_honorifica',
            'estado_logico' => 'tpu_estado_logico',
        ],
    ],

    'metodologia_investigacions' => [
        'primary_key' => 'mei_codigo',
        'columns' => [
            'id' => 'mei_codigo',
            'nombre' => 'mei_nombre',
            'descripcion' => 'mei_descripcion',
            'estado_logico' => 'mei_estado_logico',
        ],
    ],

    'tipo_investigacions' => [
        'primary_key' => 'tin_codigo',
        'columns' => [
            'id' => 'tin_codigo',
            'nombre' => 'tin_nombre',
            'descripcion' => 'tin_descripcion',
            'estado_logico' => 'tin_estado_logico',
        ],
    ],

    'componentes' => [
        'primary_key' => 'comp_codigo',
        'columns' => [
            'id' => 'comp_codigo',
            'nombre' => 'comp_nombre',
            'coordinacion_id' => 'coord_codigo',
            'anio' => 'comp_anio',
            'es_obligatorio' => 'comp_es_obligatorio',
            'estado_logico' => 'comp_estado_logico',
        ],
        'values' => [
            'es_obligatorio' => [
                true => 1,
                false => 0,
                1 => 1,
                0 => 0,
            ],
            'estado_logico' => [
                true => 1,
                false => 0,
                1 => 1,
                0 => 0,
            ],
        ],
    ],

];
