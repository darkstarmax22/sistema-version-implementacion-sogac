# Session Summary — Jun 5, 2026

## Problems Solved

### 1. `org_nombre_contacto` cannot be null
- **File**: `app/Livewire/OrganizacionManager.php`
- **Fix**: Changed null fallback for `nombre_contacto`, `apellido_contacto`, `numero_contacto` from `null` to `'-'` in `guardarOrg()`.

### 2. `org_correo` column missing from DB
- **Migration**: `database/migrations/2026_06_05_100000_add_org_correo_to_organizacion_table.php`
- **Status**: ✅ Run
- Adds `org_correo` (varchar 255, nullable) to `organizacion` table.

### 3. `com_codigo` FK missing from `comunidad_contactos`
- **Migration**: `database/migrations/2026_06_05_100001_add_com_codigo_to_comunidad_contactos_table.php`
- **Status**: ✅ Run
- Adds `com_codigo` (bigint, NOT NULL, cascade on delete) to `comunidad_contactos`.

### 4. Academic fields (`trayecto`, `programa`, `seccion`) removed from `Comunidad`
- These columns don't exist in DB (user removed them — data comes from intranet).
- **Removed from**: `config/repositorio_schema.php`, `app/Models/Comunidad.php` (fillable), `app/Services/ComunidadGestionService.php` (validation, form-load, save queries), `app/Livewire/ComunidadManager.php` (properties, rules, reset, save), `resources/views/livewire/comunidad-manager.blade.php` (form selects, table columns).
- `ComunidadGestionService::datosVistaFormulario()` signature changed — removed `$programaId` parameter.
- Removed `Programa`, `Seccion`, `DbHelper`, `Collection`, `LengthAwarePaginator` imports from service.

### 5. Encargado fields removed from `Comunidad`
- `com_nombre_encargado`, `com_apellido_encargado`, `com_telefono_encargado` columns removed from DB by user.
- **Removed from**: `config/repositorio_schema.php` (encargado mappings), `app/Models/Comunidad.php` (fillable), `app/Livewire/ComunidadManager.php` (properties, reset, save, `guardarContactosAhora`), `resources/views/livewire/comunidad-manager.blade.php` (encargado section).

### 6. `ComunidadContacto` primary key wrong
- **File**: `app/Models/ComunidadContacto.php`
- **Fix**: `protected $primaryKey = 'ccon_codigo'` → `'ccom_codigo'`.

### 7. Custom cargo in contactos
- **Config**: `config/comunidades.php` — `cargos_contacto` list: `['responsable', 'autoridad']`.
- **View**: Select includes `"+ Otro (escribir)..."` option; when selected, shows text input.
- **Livewire**: `setCargoSeleccion()` toggles input; `normalizarContactos()` ensures custom cargo is stored in `ccon_cargo`.
- **Service**: `cargarParaEdicion()` detects custom cargos (not in canonical list) and sets `cargo_custom`.

### 8. Migration `create_grupo_proyecto_modulo_table` was pending
- **File**: `database/migrations/2026_05_26_100000_create_grupo_proyecto_modulo_table.php`
- **Status**: ✅ Ran (was `Pending`, now in Batch 3)
- **Root cause**: Table didn't exist → `tablaDisponible()` returned `false` → form never loaded → register button did nothing.

## Key Patterns
- `MapsLegacyColumns` trait only works on Model instances (after `get()`). The `LegacyColumnBuilder` only overrides `where()` and `orderBy()` — all other QB methods (`whereIn`, `whereNotNull`, `whereNull`, `pluck`, `select`, `groupBy`, `update`, `delete`, etc.) bypass the mapping.
- **Fix rule**: For `whereIn()`, `whereNotNull()`, `whereNull()` — use the **physical column name** directly.
- **Fix rule**: For `update()` on QB — fetch model instance first, then `->fill($data)->save()`.
- **Fix rule**: For `pluck()` on QB — use `->get()->pluck('col')` (Collection pluck uses model accessor).
- **Fix rule**: For `select()` on QB — use physical column names, or fetch model and access attributes.
- Academic data (trayecto, programa, seccion, encargado) comes from intranet PostgreSQL — not stored in MySQL repositorio.
- `comunidad_contactos.ccon_cargo` is a varchar — custom cargos stored directly, no separate cargos table.
- `create_grupo_proyecto_modulo_table` migration must be run before users can register project teams.
