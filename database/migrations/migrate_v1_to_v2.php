<?php
/**
 * Migración v1 → v2 — Regalos Purranque
 * ═══════════════════════════════════════
 *
 * Ejecutar UNA SOLA VEZ después de instalar v2 en producción.
 * ELIMINAR este archivo inmediatamente después de la migración.
 *
 * Modos:
 *   ?key=CLAVE&mode=dry   → Solo verificar, NO inserta nada
 *   ?key=CLAVE&mode=run   → Ejecutar migración real
 *
 * Requisitos:
 *   - Tablas v2 ya creadas (schema.sql importado)
 *   - Tablas v1 presentes en la misma BD
 *   - Seed.sql NO importado (este script migra los datos reales)
 */

// ══════════════════════════════════════════════════════════════
// CONFIGURACIÓN
// ══════════════════════════════════════════════════════════════

$MIGRATION_KEY = 'regalos_migrate_2026_secure';

// Credenciales BD (misma BD para v1 y v2)
$DB_HOST    = 'localhost';
$DB_NAME    = 'purranque_regalos_purranque';
$DB_USER    = 'purranque_admin';
$DB_PASS    = '';  // COMPLETAR con contraseña real
$DB_CHARSET = 'utf8mb4';

// Prefijo de tablas v1 (dejar vacío si no tienen prefijo)
$V1_PREFIX = '';

// Sufijo para renombrar tablas v1 antes de crear v2
$V1_SUFFIX = '_v1';

// Ruta base del sitio en el servidor
$BASE_PATH = dirname(dirname(__DIR__));

// ══════════════════════════════════════════════════════════════
// SEGURIDAD
// ══════════════════════════════════════════════════════════════

if (php_sapi_name() !== 'cli') {
    if (($_GET['key'] ?? '') !== $MIGRATION_KEY) {
        http_response_code(403);
        die('Acceso denegado');
    }
}

$MODE = $_GET['mode'] ?? (($argv[1] ?? '') ?: 'dry');
if (!in_array($MODE, ['dry', 'run'])) {
    die('Modo inválido. Use mode=dry o mode=run');
}

$DRY_RUN = ($MODE === 'dry');

// ══════════════════════════════════════════════════════════════
// CONEXIÓN BD
// ══════════════════════════════════════════════════════════════

set_time_limit(300);
ini_set('memory_limit', '256M');

header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">';
echo '<title>Migración v1 → v2</title>';
echo '<style>
body { font-family: system-ui, sans-serif; max-width: 960px; margin: 40px auto; padding: 0 20px; background: #0f172a; color: #e2e8f0; }
h1 { color: #38bdf8; border-bottom: 2px solid #1e3a5f; padding-bottom: 10px; }
h2 { color: #7dd3fc; margin-top: 30px; }
.ok { color: #4ade80; }
.warn { color: #fbbf24; }
.err { color: #f87171; }
.info { color: #94a3b8; }
.count { color: #c084fc; font-weight: bold; }
pre { background: #1e293b; padding: 15px; border-radius: 8px; overflow-x: auto; font-size: 13px; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
.badge-dry { background: #fbbf24; color: #1e293b; }
.badge-run { background: #f87171; color: #fff; }
.summary { background: #1e3a5f; padding: 20px; border-radius: 8px; margin-top: 30px; }
.summary h3 { color: #38bdf8; margin-top: 0; }
.summary li { margin: 5px 0; }
</style></head><body>';

echo '<h1>Migración v1 → v2 — Regalos Purranque</h1>';
echo '<p>Modo: <span class="badge badge-' . ($DRY_RUN ? 'dry' : 'run') . '">' . ($DRY_RUN ? 'DRY RUN (solo verificación)' : 'EJECUCIÓN REAL') . '</span></p>';
flush();

$log = [];
$stats = [
    'comercios'           => 0,
    'categorias'          => 0,
    'comercio_categoria'  => 0,
    'fechas_especiales'   => 0,
    'comercio_fecha'      => 0,
    'comercio_fotos'      => 0,
    'comercio_horarios'   => 0,
    'noticias'            => 0,
    'noticia_categoria'   => 0,
    'noticia_fecha'       => 0,
    'banners'             => 0,
    'resenas'             => 0,
    'seo_config'          => 0,
    'seo_redirects'       => 0,
    'analytics_diario'    => 0,
    'configuracion'       => 0,
    'admin_usuarios'      => 0,
    'imagenes_copiadas'   => 0,
    'imagenes_faltantes'  => 0,
    'errores'             => 0,
];

function logMsg(string $type, string $msg): void {
    global $log;
    $class = match($type) {
        'ok'   => 'ok',
        'warn' => 'warn',
        'err'  => 'err',
        default => 'info',
    };
    $prefix = match($type) {
        'ok'   => '✓',
        'warn' => '⚠',
        'err'  => '✗',
        default => '→',
    };
    $line = "<span class=\"{$class}\">{$prefix} {$msg}</span><br>";
    echo $line;
    $log[] = strip_tags($line);
    flush();
}

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    logMsg('ok', 'Conexión a BD exitosa: ' . $DB_NAME);
} catch (PDOException $e) {
    logMsg('err', 'Error de conexión: ' . $e->getMessage());
    echo '</body></html>';
    exit(1);
}

// ══════════════════════════════════════════════════════════════
// PASO 0: DETECTAR TABLAS V1
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 0: Detección de tablas</h2>';

$allTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
logMsg('info', 'Tablas encontradas en BD: ' . count($allTables));

// Tablas que v2 necesita (las que crea schema.sql)
$v2Tables = [
    'admin_usuarios', 'sesiones_admin', 'admin_log', 'configuracion',
    'comercios', 'categorias', 'comercio_categoria',
    'fechas_especiales', 'comercio_fecha', 'comercio_fotos', 'comercio_horarios',
    'noticias', 'noticia_categoria', 'noticia_fecha', 'banners',
    'resenas', 'resenas_reportes',
    'seo_config', 'seo_redirects', 'visitas_log', 'analytics_diario',
    'configuracion_mantenimiento',
];

// Detectar si las tablas v1 ya fueron renombradas
$v1Renamed = false;
foreach ($v2Tables as $t) {
    if (in_array($t . $V1_SUFFIX, $allTables)) {
        $v1Renamed = true;
        break;
    }
}

// Detectar si tablas v2 ya están creadas (vacías, con estructura v2)
$v2Created = false;
foreach ($v2Tables as $t) {
    if (in_array($t, $allTables)) {
        $v2Created = true;
        break;
    }
}

if ($v1Renamed) {
    logMsg('ok', 'Tablas v1 ya renombradas (sufijo ' . $V1_SUFFIX . '). Continuando migración de datos.');
} elseif ($v2Created) {
    // Las tablas existen — podrían ser v1 (con datos) o v2 (vacías)
    // Verificar si son v1 comprobando si tienen datos
    // Verificar si son v1 (con datos) o v2 vacías (recién creadas con schema.sql)
    $comerciosCount = (int) $pdo->query("SELECT COUNT(*) FROM comercios")->fetchColumn();
    $categoriasCount = (int) $pdo->query("SELECT COUNT(*) FROM categorias")->fetchColumn();
    $totalRows = $comerciosCount + $categoriasCount;

    if ($comerciosCount > 0) {
        logMsg('info', "Tablas con nombre v2 encontradas con {$comerciosCount} comercios — son tablas v1");
        logMsg('info', 'Se renombrarán tablas v1 → sufijo ' . $V1_SUFFIX);
    } else {
        logMsg('ok', 'Tablas v2 vacías detectadas. Schema ya importado.');
        // Marcar como si ya estuvieran renombradas para omitir el paso de renombrado
        $v1Renamed = true;
    }
}

// ══════════════════════════════════════════════════════════════
// PASO 1: RENOMBRAR TABLAS V1 (si no están renombradas)
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 1: Preparar tablas</h2>';

// Lista de tablas v1 conocidas que deben renombrarse
// (solo las que tienen el mismo nombre que v2)
$tablesToRename = [
    'comercios', 'categorias', 'comercio_categoria',
    'fechas_especiales', 'comercio_fecha', 'comercio_fotos', 'comercio_horarios',
    'noticias', 'noticia_categoria', 'noticia_fecha', 'banners',
    'resenas', 'resenas_reportes',
    'seo_config', 'seo_redirects', 'visitas_log', 'analytics_diario',
    'configuracion', 'configuracion_mantenimiento',
    'admin_usuarios', 'sesiones_admin', 'admin_log',
];

if (!$v1Renamed) {
    // Verificar que existen tablas v1 para renombrar
    $existingV1 = array_filter($tablesToRename, function($t) use ($allTables) {
        return in_array($t, $allTables);
    });

    if (empty($existingV1)) {
        logMsg('warn', 'No se encontraron tablas v1 para renombrar. ¿Ya importó schema.sql sin datos v1?');
    } else {
        // Deshabilitar FK checks para renombrar
        if (!$DRY_RUN) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        }

        foreach ($existingV1 as $table) {
            $newName = $table . $V1_SUFFIX;
            if (in_array($newName, $allTables)) {
                logMsg('warn', "Tabla {$newName} ya existe, omitiendo renombrar {$table}");
                continue;
            }
            if ($DRY_RUN) {
                logMsg('info', "[DRY] Renombraría: {$table} → {$newName}");
            } else {
                $pdo->exec("RENAME TABLE `{$table}` TO `{$newName}`");
                logMsg('ok', "Renombrada: {$table} → {$newName}");
            }
        }

        // Re-habilitar FK checks
        if (!$DRY_RUN) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        }
    }
}

// ══════════════════════════════════════════════════════════════
// PASO 2: CREAR TABLAS V2 (importar schema.sql)
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 2: Crear tablas v2</h2>';

$schemaFile = $BASE_PATH . '/database/schema.sql';
if (!file_exists($schemaFile)) {
    logMsg('err', 'No se encontró schema.sql en: ' . $schemaFile);
    echo '</body></html>';
    exit(1);
}

// Verificar si las tablas v2 ya existen (creadas previamente)
$tablesAfterRename = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
$v2AlreadyExist = in_array('comercios', $tablesAfterRename);

if ($v2AlreadyExist) {
    logMsg('ok', 'Tablas v2 ya existen. Omitiendo importación de schema.sql.');
} else {
    if ($DRY_RUN) {
        logMsg('info', '[DRY] Importaría schema.sql para crear tablas v2');
    } else {
        $schemaSql = file_get_contents($schemaFile);
        // Ejecutar cada sentencia por separado
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
        $statements = array_filter(
            array_map('trim', explode(';', $schemaSql)),
            function($s) { return !empty($s) && stripos($s, 'CREATE TABLE') !== false; }
        );
        foreach ($statements as $stmt) {
            try {
                $pdo->exec($stmt);
            } catch (PDOException $e) {
                // IF NOT EXISTS — ignorar si ya existe
                if (strpos($e->getMessage(), 'already exists') === false) {
                    logMsg('warn', 'Schema warning: ' . $e->getMessage());
                }
            }
        }
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        logMsg('ok', 'Tablas v2 creadas desde schema.sql');
    }
}

// ══════════════════════════════════════════════════════════════
// HELPERS
// ══════════════════════════════════════════════════════════════

/**
 * Verificar si una tabla v1 (renombrada) existe
 */
function v1TableExists(PDO $pdo, string $table, string $suffix): bool {
    $name = $table . $suffix;
    $dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?"
    );
    $stmt->execute([$dbName, $name]);
    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Obtener nombre de tabla v1
 */
function v1(string $table): string {
    global $V1_SUFFIX;
    return $table . $V1_SUFFIX;
}

/**
 * Copiar imagen de ruta v1 a estructura v2
 * Retorna la ruta v2 o la ruta original si no necesita cambio
 */
function migrateImage(string $rutaV1, string $subdir, bool $dryRun): ?string {
    global $BASE_PATH, $stats;

    if (empty($rutaV1)) return null;

    // Normalizar ruta — quitar dominio si tiene URL completa
    $ruta = $rutaV1;
    $ruta = preg_replace('#^https?://[^/]+#', '', $ruta);

    // Si ya tiene el formato v2 (/assets/img/...), verificar que existe
    $fullPath = $BASE_PATH . $ruta;
    if (file_exists($fullPath)) {
        $stats['imagenes_copiadas']++;
        return $ruta;
    }

    // Intentar variantes comunes de v1
    $possiblePaths = [
        $BASE_PATH . $ruta,
        $BASE_PATH . '/assets/img/' . basename($ruta),
        $BASE_PATH . '/assets/img/' . $subdir . '/' . basename($ruta),
        $BASE_PATH . '/uploads/' . basename($ruta),
        $BASE_PATH . '/img/' . basename($ruta),
    ];

    foreach ($possiblePaths as $srcPath) {
        if (file_exists($srcPath)) {
            $destDir = $BASE_PATH . '/assets/img/' . $subdir;
            $destPath = $destDir . '/' . basename($ruta);
            $destRuta = '/assets/img/' . $subdir . '/' . basename($ruta);

            if (!$dryRun) {
                if (!is_dir($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                if ($srcPath !== $destPath) {
                    copy($srcPath, $destPath);
                }
            }
            $stats['imagenes_copiadas']++;
            return $destRuta;
        }
    }

    // Imagen no encontrada
    $stats['imagenes_faltantes']++;
    return $ruta; // Mantener ruta original
}

// ══════════════════════════════════════════════════════════════
// PASO 3: MIGRAR ADMIN USUARIOS
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 3: Migrar usuarios admin</h2>';

if (v1TableExists($pdo, 'admin_usuarios', $V1_SUFFIX)) {
    $v1Users = $pdo->query("SELECT * FROM `" . v1('admin_usuarios') . "`")->fetchAll();
    logMsg('info', 'Usuarios v1 encontrados: ' . count($v1Users));

    foreach ($v1Users as $user) {
        if ($DRY_RUN) {
            logMsg('info', "[DRY] Migraría usuario: {$user['nombre']} ({$user['email']}) — rol: {$user['rol']}");
        } else {
            // Verificar si ya existe por email
            $exists = $pdo->prepare("SELECT id FROM admin_usuarios WHERE email = ?");
            $exists->execute([$user['email']]);
            if ($exists->fetch()) {
                logMsg('warn', "Usuario ya existe: {$user['email']}. Omitido.");
                continue;
            }

            $stmt = $pdo->prepare("INSERT INTO admin_usuarios
                (nombre, email, telefono, password_hash, rol, avatar, activo, last_login, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $user['nombre'],
                $user['email'],
                $user['telefono'] ?? null,
                $user['password_hash'],
                $user['rol'] ?? 'editor',
                $user['avatar'] ?? null,
                $user['activo'] ?? 1,
                $user['last_login'] ?? null,
                $user['created_at'] ?? date('Y-m-d H:i:s'),
            ]);
            logMsg('ok', "Usuario migrado: {$user['nombre']}");
        }
        $stats['admin_usuarios']++;
    }
} else {
    logMsg('warn', 'Tabla ' . v1('admin_usuarios') . ' no encontrada. Creando usuario admin por defecto.');
    if (!$DRY_RUN) {
        $exists = $pdo->prepare("SELECT id FROM admin_usuarios WHERE email = ?");
        $exists->execute(['admin@regalos.purranque.info']);
        if (!$exists->fetch()) {
            $pdo->exec("INSERT INTO admin_usuarios (nombre, email, password_hash, rol, activo) VALUES
                ('Administrador', 'admin@regalos.purranque.info',
                 '" . password_hash('CambiarEstaContraseña2026', PASSWORD_DEFAULT) . "', 'admin', 1)");
            logMsg('ok', 'Usuario admin creado. CAMBIAR CONTRASEÑA después de migrar.');
        }
    }
}

// ══════════════════════════════════════════════════════════════
// PASO 4: MIGRAR CATEGORÍAS
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 4: Migrar categorías</h2>';

// Mapeo de IDs v1 → v2 para categorías
$catMapV1toV2 = [];

if (v1TableExists($pdo, 'categorias', $V1_SUFFIX)) {
    $v1Cats = $pdo->query("SELECT * FROM `" . v1('categorias') . "` ORDER BY id")->fetchAll();
    logMsg('info', 'Categorías v1 encontradas: ' . count($v1Cats));

    foreach ($v1Cats as $cat) {
        if ($DRY_RUN) {
            logMsg('info', "[DRY] Migraría categoría: {$cat['nombre']} (slug: {$cat['slug']})");
        } else {
            // Verificar si ya existe por slug
            $exists = $pdo->prepare("SELECT id FROM categorias WHERE slug = ?");
            $exists->execute([$cat['slug']]);
            $existing = $exists->fetch();

            if ($existing) {
                $catMapV1toV2[$cat['id']] = $existing['id'];
                logMsg('info', "Categoría ya existe: {$cat['nombre']} → ID v2: {$existing['id']}");
                continue;
            }

            // Migrar imagen
            $imagen = migrateImage($cat['imagen'] ?? '', 'categorias', $DRY_RUN);

            $stmt = $pdo->prepare("INSERT INTO categorias
                (nombre, slug, descripcion, icono, imagen, color, orden, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $cat['nombre'],
                $cat['slug'],
                $cat['descripcion'] ?? null,
                $cat['icono'] ?? null,
                $imagen,
                $cat['color'] ?? '#2563eb',
                $cat['orden'] ?? 0,
                $cat['activo'] ?? 1,
            ]);
            $newId = (int) $pdo->lastInsertId();
            $catMapV1toV2[$cat['id']] = $newId;
            logMsg('ok', "Categoría migrada: {$cat['nombre']} → ID v2: {$newId}");
        }
        $stats['categorias']++;
    }
} else {
    logMsg('warn', 'Tabla ' . v1('categorias') . ' no encontrada. Se usarán categorías del seed.');
    // Cargar categorías existentes v2 para mapeo
    $v2Cats = $pdo->query("SELECT id, slug FROM categorias")->fetchAll();
    foreach ($v2Cats as $c) {
        $catMapV1toV2[$c['id']] = $c['id']; // Mapeo directo si IDs coinciden
    }
}

// ══════════════════════════════════════════════════════════════
// PASO 5: MIGRAR FECHAS ESPECIALES
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 5: Migrar fechas especiales</h2>';

$fechaMapV1toV2 = [];

// Mapeo de slugs v1 → slugs v2 (para las que difieren)
$slugMapping = [
    'pascua'                => 'pascua-de-resurreccion',
    'aniversario-matrimonio'=> 'aniversario-de-matrimonio',
    'cumple-mes-pololeo'    => 'cumple-mes-de-pololeo',
    'bodas-matrimonios'     => 'bodas-y-matrimonios',
    'despedida-soltero'     => 'despedida-de-solteroa',
];

if (v1TableExists($pdo, 'fechas_especiales', $V1_SUFFIX)) {
    $v1Fechas = $pdo->query("SELECT * FROM `" . v1('fechas_especiales') . "` ORDER BY id")->fetchAll();
    logMsg('info', 'Fechas v1 encontradas: ' . count($v1Fechas));

    foreach ($v1Fechas as $fecha) {
        $slugV2 = $slugMapping[$fecha['slug']] ?? $fecha['slug'];

        if ($DRY_RUN) {
            $slugNote = ($slugV2 !== $fecha['slug']) ? " (slug v1: {$fecha['slug']} → v2: {$slugV2})" : '';
            logMsg('info', "[DRY] Migraría fecha: {$fecha['nombre']}{$slugNote}");
        } else {
            // Buscar si existe en v2 por slug mapeado
            $exists = $pdo->prepare("SELECT id FROM fechas_especiales WHERE slug = ?");
            $exists->execute([$slugV2]);
            $existing = $exists->fetch();

            if ($existing) {
                $fechaMapV1toV2[$fecha['id']] = $existing['id'];
                logMsg('info', "Fecha ya existe: {$fecha['nombre']} → ID v2: {$existing['id']}");
                continue;
            }

            // Intentar por slug original si no se encontró
            if ($slugV2 !== $fecha['slug']) {
                $exists->execute([$fecha['slug']]);
                $existing = $exists->fetch();
                if ($existing) {
                    $fechaMapV1toV2[$fecha['id']] = $existing['id'];
                    logMsg('info', "Fecha encontrada por slug original: {$fecha['nombre']} → ID v2: {$existing['id']}");
                    continue;
                }
            }

            // Crear nueva fecha
            $imagen = migrateImage($fecha['imagen'] ?? '', 'fechas', $DRY_RUN);
            $tipo = $fecha['tipo'] ?? 'calendario';
            // Asegurar que tipo es válido para ENUM
            if (!in_array($tipo, ['personal', 'calendario', 'comercial'])) {
                $tipo = 'calendario';
            }

            $stmt = $pdo->prepare("INSERT INTO fechas_especiales
                (nombre, slug, descripcion, tipo, icono, imagen, fecha_inicio, fecha_fin, recurrente, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $fecha['nombre'],
                $slugV2,
                $fecha['descripcion'] ?? null,
                $tipo,
                $fecha['icono'] ?? null,
                $imagen,
                $fecha['fecha_inicio'] ?? null,
                $fecha['fecha_fin'] ?? null,
                $fecha['recurrente'] ?? 0,
                $fecha['activo'] ?? 1,
            ]);
            $newId = (int) $pdo->lastInsertId();
            $fechaMapV1toV2[$fecha['id']] = $newId;
            logMsg('ok', "Fecha migrada: {$fecha['nombre']} → ID v2: {$newId}");
        }
        $stats['fechas_especiales']++;
    }
} else {
    logMsg('warn', 'Tabla ' . v1('fechas_especiales') . ' no encontrada. Se usarán fechas del seed.');
    $v2Fechas = $pdo->query("SELECT id, slug FROM fechas_especiales")->fetchAll();
    foreach ($v2Fechas as $f) {
        $fechaMapV1toV2[$f['id']] = $f['id'];
    }
}

// ══════════════════════════════════════════════════════════════
// PASO 6: MIGRAR COMERCIOS
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 6: Migrar comercios</h2>';

$comercioMapV1toV2 = [];

if (v1TableExists($pdo, 'comercios', $V1_SUFFIX)) {
    $v1Comercios = $pdo->query("SELECT * FROM `" . v1('comercios') . "` ORDER BY id")->fetchAll();
    logMsg('info', 'Comercios v1 encontrados: ' . count($v1Comercios));

    foreach ($v1Comercios as $com) {
        if ($DRY_RUN) {
            logMsg('info', "[DRY] Migraría comercio: {$com['nombre']} (slug: {$com['slug']}, plan: {$com['plan']})");
        } else {
            // Verificar si ya existe por slug
            $exists = $pdo->prepare("SELECT id FROM comercios WHERE slug = ?");
            $exists->execute([$com['slug']]);
            $existing = $exists->fetch();

            if ($existing) {
                $comercioMapV1toV2[$com['id']] = $existing['id'];
                logMsg('warn', "Comercio ya existe: {$com['nombre']}. Omitido.");
                continue;
            }

            // Migrar imágenes
            $logo    = migrateImage($com['logo'] ?? '', 'logos', $DRY_RUN);
            $portada = migrateImage($com['portada'] ?? '', 'portadas', $DRY_RUN);

            // Mapear plan (asegurar que es válido para ENUM v2)
            $plan = strtolower($com['plan'] ?? 'basico');
            if (!in_array($plan, ['basico', 'premium', 'sponsor'])) {
                $plan = 'basico';
            }

            $stmt = $pdo->prepare("INSERT INTO comercios
                (nombre, slug, descripcion, telefono, whatsapp, email, sitio_web,
                 direccion, lat, lng, logo, portada, plan, activo, destacado,
                 visitas, whatsapp_clicks, seo_titulo, seo_descripcion, seo_keywords, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $com['nombre'],
                $com['slug'],
                $com['descripcion'] ?? null,
                $com['telefono'] ?? null,
                $com['whatsapp'] ?? null,
                $com['email'] ?? null,
                $com['sitio_web'] ?? null,
                $com['direccion'] ?? null,
                $com['lat'] ?? null,
                $com['lng'] ?? null,
                $logo,
                $portada,
                $plan,
                $com['activo'] ?? 1,
                $com['destacado'] ?? 0,
                $com['visitas'] ?? 0,
                $com['whatsapp_clicks'] ?? 0,
                $com['seo_titulo'] ?? null,
                $com['seo_descripcion'] ?? null,
                $com['seo_keywords'] ?? null,
                $com['created_at'] ?? date('Y-m-d H:i:s'),
            ]);
            $newId = (int) $pdo->lastInsertId();
            $comercioMapV1toV2[$com['id']] = $newId;
            logMsg('ok', "Comercio migrado: {$com['nombre']} → ID v2: {$newId}");
        }
        $stats['comercios']++;
    }
} else {
    logMsg('err', 'Tabla ' . v1('comercios') . ' no encontrada. No se pueden migrar comercios.');
}

// ══════════════════════════════════════════════════════════════
// PASO 7: MIGRAR RELACIONES COMERCIO-CATEGORÍA
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 7: Migrar relaciones comercio-categoría</h2>';

if (v1TableExists($pdo, 'comercio_categoria', $V1_SUFFIX)) {
    $v1Rels = $pdo->query("SELECT * FROM `" . v1('comercio_categoria') . "`")->fetchAll();
    logMsg('info', 'Relaciones comercio-categoría v1: ' . count($v1Rels));

    foreach ($v1Rels as $rel) {
        $comV2 = $comercioMapV1toV2[$rel['comercio_id']] ?? null;
        $catV2 = $catMapV1toV2[$rel['categoria_id']] ?? null;

        if (!$comV2 || !$catV2) {
            logMsg('warn', "Relación omitida — comercio:{$rel['comercio_id']} o categoría:{$rel['categoria_id']} no mapeados");
            continue;
        }

        if ($DRY_RUN) {
            logMsg('info', "[DRY] Vincularía comercio v2:{$comV2} → categoría v2:{$catV2}");
        } else {
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO comercio_categoria
                    (comercio_id, categoria_id, es_principal) VALUES (?, ?, ?)");
                $stmt->execute([$comV2, $catV2, $rel['es_principal'] ?? 0]);
            } catch (PDOException $e) {
                logMsg('warn', "Error en relación comercio-categoría: " . $e->getMessage());
            }
        }
        $stats['comercio_categoria']++;
    }
} else {
    logMsg('warn', 'Tabla ' . v1('comercio_categoria') . ' no encontrada.');
}

// ══════════════════════════════════════════════════════════════
// PASO 8: MIGRAR RELACIONES COMERCIO-FECHA
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 8: Migrar relaciones comercio-fecha</h2>';

if (v1TableExists($pdo, 'comercio_fecha', $V1_SUFFIX)) {
    $v1Rels = $pdo->query("SELECT * FROM `" . v1('comercio_fecha') . "`")->fetchAll();
    logMsg('info', 'Relaciones comercio-fecha v1: ' . count($v1Rels));

    foreach ($v1Rels as $rel) {
        $comV2   = $comercioMapV1toV2[$rel['comercio_id']] ?? null;
        $fechaV2 = $fechaMapV1toV2[$rel['fecha_id']] ?? null;

        if (!$comV2 || !$fechaV2) {
            logMsg('warn', "Relación omitida — comercio:{$rel['comercio_id']} o fecha:{$rel['fecha_id']} no mapeados");
            continue;
        }

        if ($DRY_RUN) {
            logMsg('info', "[DRY] Vincularía comercio v2:{$comV2} → fecha v2:{$fechaV2}");
        } else {
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO comercio_fecha
                    (comercio_id, fecha_id, oferta_especial, precio_desde, precio_hasta, activo)
                    VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $comV2, $fechaV2,
                    $rel['oferta_especial'] ?? null,
                    $rel['precio_desde'] ?? null,
                    $rel['precio_hasta'] ?? null,
                    $rel['activo'] ?? 1,
                ]);
            } catch (PDOException $e) {
                logMsg('warn', "Error en relación comercio-fecha: " . $e->getMessage());
            }
        }
        $stats['comercio_fecha']++;
    }
} else {
    logMsg('warn', 'Tabla ' . v1('comercio_fecha') . ' no encontrada.');
}

// ══════════════════════════════════════════════════════════════
// PASO 9: MIGRAR FOTOS DE COMERCIOS
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 9: Migrar fotos de comercios</h2>';

if (v1TableExists($pdo, 'comercio_fotos', $V1_SUFFIX)) {
    $v1Fotos = $pdo->query("SELECT * FROM `" . v1('comercio_fotos') . "` ORDER BY comercio_id, orden")->fetchAll();
    logMsg('info', 'Fotos v1 encontradas: ' . count($v1Fotos));

    foreach ($v1Fotos as $foto) {
        $comV2 = $comercioMapV1toV2[$foto['comercio_id']] ?? null;
        if (!$comV2) {
            logMsg('warn', "Foto omitida — comercio:{$foto['comercio_id']} no mapeado");
            continue;
        }

        $ruta      = migrateImage($foto['ruta'] ?? '', 'galeria', $DRY_RUN);
        $rutaThumb = migrateImage($foto['ruta_thumb'] ?? '', 'galeria', $DRY_RUN);

        if ($DRY_RUN) {
            logMsg('info', "[DRY] Migraría foto de comercio v2:{$comV2}");
        } else {
            $stmt = $pdo->prepare("INSERT INTO comercio_fotos
                (comercio_id, ruta, ruta_thumb, titulo, orden) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $comV2,
                $ruta,
                $rutaThumb,
                $foto['titulo'] ?? null,
                $foto['orden'] ?? 0,
            ]);
        }
        $stats['comercio_fotos']++;
    }
} else {
    logMsg('info', 'Tabla ' . v1('comercio_fotos') . ' no encontrada. Omitido.');
}

// ══════════════════════════════════════════════════════════════
// PASO 10: MIGRAR HORARIOS DE COMERCIOS
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 10: Migrar horarios de comercios</h2>';

if (v1TableExists($pdo, 'comercio_horarios', $V1_SUFFIX)) {
    $v1Horarios = $pdo->query("SELECT * FROM `" . v1('comercio_horarios') . "`")->fetchAll();
    logMsg('info', 'Horarios v1 encontrados: ' . count($v1Horarios));

    foreach ($v1Horarios as $h) {
        $comV2 = $comercioMapV1toV2[$h['comercio_id']] ?? null;
        if (!$comV2) continue;

        if ($DRY_RUN) {
            logMsg('info', "[DRY] Migraría horario comercio v2:{$comV2} día:{$h['dia']}");
        } else {
            $stmt = $pdo->prepare("INSERT IGNORE INTO comercio_horarios
                (comercio_id, dia, hora_apertura, hora_cierre, cerrado) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $comV2,
                $h['dia'],
                $h['hora_apertura'] ?? null,
                $h['hora_cierre'] ?? null,
                $h['cerrado'] ?? 0,
            ]);
        }
        $stats['comercio_horarios']++;
    }
} else {
    logMsg('info', 'Tabla ' . v1('comercio_horarios') . ' no encontrada. Omitido.');
}

// ══════════════════════════════════════════════════════════════
// PASO 11: MIGRAR NOTICIAS
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 11: Migrar noticias</h2>';

$noticiaMapV1toV2 = [];

if (v1TableExists($pdo, 'noticias', $V1_SUFFIX)) {
    $v1Noticias = $pdo->query("SELECT * FROM `" . v1('noticias') . "` ORDER BY id")->fetchAll();
    logMsg('info', 'Noticias v1 encontradas: ' . count($v1Noticias));

    foreach ($v1Noticias as $not) {
        if ($DRY_RUN) {
            logMsg('info', "[DRY] Migraría noticia: {$not['titulo']} (slug: {$not['slug']})");
        } else {
            // Verificar duplicado por slug
            $exists = $pdo->prepare("SELECT id FROM noticias WHERE slug = ?");
            $exists->execute([$not['slug']]);
            $existing = $exists->fetch();

            if ($existing) {
                $noticiaMapV1toV2[$not['id']] = $existing['id'];
                logMsg('warn', "Noticia ya existe: {$not['titulo']}. Omitida.");
                continue;
            }

            $imagen   = migrateImage($not['imagen'] ?? '', 'noticias', $DRY_RUN);
            $imagenOg = migrateImage($not['seo_imagen_og'] ?? '', 'og', $DRY_RUN);

            $stmt = $pdo->prepare("INSERT INTO noticias
                (titulo, slug, contenido, extracto, imagen, autor, activo, destacada,
                 seo_titulo, seo_descripcion, seo_keywords, seo_imagen_og, seo_noindex,
                 fecha_publicacion, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $not['titulo'],
                $not['slug'],
                $not['contenido'] ?? null,
                $not['extracto'] ?? null,
                $imagen,
                $not['autor'] ?? null,
                $not['activo'] ?? 1,
                $not['destacada'] ?? 0,
                $not['seo_titulo'] ?? null,
                $not['seo_descripcion'] ?? null,
                $not['seo_keywords'] ?? null,
                $imagenOg,
                $not['seo_noindex'] ?? 0,
                $not['fecha_publicacion'] ?? $not['created_at'] ?? date('Y-m-d H:i:s'),
                $not['created_at'] ?? date('Y-m-d H:i:s'),
            ]);
            $newId = (int) $pdo->lastInsertId();
            $noticiaMapV1toV2[$not['id']] = $newId;
            logMsg('ok', "Noticia migrada: {$not['titulo']} → ID v2: {$newId}");
        }
        $stats['noticias']++;
    }
} else {
    logMsg('info', 'Tabla ' . v1('noticias') . ' no encontrada. Omitido.');
}

// ══════════════════════════════════════════════════════════════
// PASO 12: MIGRAR RELACIONES NOTICIA-CATEGORÍA Y NOTICIA-FECHA
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 12: Migrar relaciones de noticias</h2>';

if (v1TableExists($pdo, 'noticia_categoria', $V1_SUFFIX)) {
    $v1Rels = $pdo->query("SELECT * FROM `" . v1('noticia_categoria') . "`")->fetchAll();
    foreach ($v1Rels as $rel) {
        $notV2 = $noticiaMapV1toV2[$rel['noticia_id']] ?? null;
        $catV2 = $catMapV1toV2[$rel['categoria_id']] ?? null;
        if (!$notV2 || !$catV2) continue;
        if (!$DRY_RUN) {
            $pdo->prepare("INSERT IGNORE INTO noticia_categoria (noticia_id, categoria_id) VALUES (?, ?)")
                ->execute([$notV2, $catV2]);
        }
        $stats['noticia_categoria']++;
    }
    logMsg('ok', 'Relaciones noticia-categoría: ' . $stats['noticia_categoria']);
}

if (v1TableExists($pdo, 'noticia_fecha', $V1_SUFFIX)) {
    $v1Rels = $pdo->query("SELECT * FROM `" . v1('noticia_fecha') . "`")->fetchAll();
    foreach ($v1Rels as $rel) {
        $notV2   = $noticiaMapV1toV2[$rel['noticia_id']] ?? null;
        $fechaV2 = $fechaMapV1toV2[$rel['fecha_id']] ?? null;
        if (!$notV2 || !$fechaV2) continue;
        if (!$DRY_RUN) {
            $pdo->prepare("INSERT IGNORE INTO noticia_fecha (noticia_id, fecha_id) VALUES (?, ?)")
                ->execute([$notV2, $fechaV2]);
        }
        $stats['noticia_fecha']++;
    }
    logMsg('ok', 'Relaciones noticia-fecha: ' . $stats['noticia_fecha']);
}

// ══════════════════════════════════════════════════════════════
// PASO 13: MIGRAR BANNERS
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 13: Migrar banners</h2>';

if (v1TableExists($pdo, 'banners', $V1_SUFFIX)) {
    $v1Banners = $pdo->query("SELECT * FROM `" . v1('banners') . "` ORDER BY id")->fetchAll();
    logMsg('info', 'Banners v1 encontrados: ' . count($v1Banners));

    foreach ($v1Banners as $ban) {
        $imagen = migrateImage($ban['imagen'] ?? '', 'banners', $DRY_RUN);
        $comV2  = isset($ban['comercio_id']) ? ($comercioMapV1toV2[$ban['comercio_id']] ?? null) : null;

        // Mapear tipo a ENUM válido
        $tipo = $ban['tipo'] ?? 'sidebar';
        if (!in_array($tipo, ['hero', 'sidebar', 'entre_comercios', 'footer'])) {
            $tipo = 'sidebar';
        }

        if ($DRY_RUN) {
            logMsg('info', "[DRY] Migraría banner: {$ban['titulo']} (tipo: {$tipo})");
        } else {
            $stmt = $pdo->prepare("INSERT INTO banners
                (titulo, tipo, imagen, url, posicion, comercio_id, activo,
                 clicks, impresiones, fecha_inicio, fecha_fin, orden)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $ban['titulo'] ?? null,
                $tipo,
                $imagen,
                $ban['url'] ?? null,
                $ban['posicion'] ?? null,
                $comV2,
                $ban['activo'] ?? 1,
                $ban['clicks'] ?? 0,
                $ban['impresiones'] ?? 0,
                $ban['fecha_inicio'] ?? null,
                $ban['fecha_fin'] ?? null,
                $ban['orden'] ?? 0,
            ]);
            logMsg('ok', "Banner migrado: " . ($ban['titulo'] ?? 'Sin título'));
        }
        $stats['banners']++;
    }
} else {
    logMsg('info', 'Tabla ' . v1('banners') . ' no encontrada. Omitido.');
}

// ══════════════════════════════════════════════════════════════
// PASO 14: MIGRAR RESEÑAS
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 14: Migrar reseñas</h2>';

if (v1TableExists($pdo, 'resenas', $V1_SUFFIX)) {
    $v1Resenas = $pdo->query("SELECT * FROM `" . v1('resenas') . "` ORDER BY id")->fetchAll();
    logMsg('info', 'Reseñas v1 encontradas: ' . count($v1Resenas));

    foreach ($v1Resenas as $res) {
        $comV2 = $comercioMapV1toV2[$res['comercio_id']] ?? null;
        if (!$comV2) {
            logMsg('warn', "Reseña omitida — comercio:{$res['comercio_id']} no mapeado");
            continue;
        }

        // Validar estado
        $estado = $res['estado'] ?? 'pendiente';
        if (!in_array($estado, ['pendiente', 'aprobada', 'rechazada'])) {
            $estado = 'pendiente';
        }

        // Validar calificación
        $calificacion = max(1, min(5, (int) ($res['calificacion'] ?? 5)));

        if ($DRY_RUN) {
            logMsg('info', "[DRY] Migraría reseña de {$res['nombre_autor']} — estado: {$estado}");
        } else {
            $stmt = $pdo->prepare("INSERT INTO resenas
                (comercio_id, nombre_autor, email_autor, calificacion, comentario,
                 estado, respuesta_comercio, fecha_respuesta, ip, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $comV2,
                $res['nombre_autor'],
                $res['email_autor'] ?? null,
                $calificacion,
                $res['comentario'] ?? null,
                $estado,
                $res['respuesta_comercio'] ?? null,
                $res['fecha_respuesta'] ?? null,
                $res['ip'] ?? null,
                $res['created_at'] ?? date('Y-m-d H:i:s'),
            ]);
            logMsg('ok', "Reseña migrada: {$res['nombre_autor']} ({$estado})");
        }
        $stats['resenas']++;
    }
} else {
    logMsg('info', 'Tabla ' . v1('resenas') . ' no encontrada. Omitido.');
}

// ══════════════════════════════════════════════════════════════
// PASO 15: MIGRAR CONFIGURACIÓN SEO
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 15: Migrar configuración SEO</h2>';

if (v1TableExists($pdo, 'seo_config', $V1_SUFFIX)) {
    $v1Seo = $pdo->query("SELECT * FROM `" . v1('seo_config') . "`")->fetchAll();
    logMsg('info', 'Config SEO v1 encontradas: ' . count($v1Seo));

    foreach ($v1Seo as $cfg) {
        if ($DRY_RUN) {
            logMsg('info', "[DRY] Migraría SEO config: {$cfg['clave']}");
        } else {
            $pdo->prepare("INSERT INTO seo_config (clave, valor) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor)")
                ->execute([$cfg['clave'], $cfg['valor']]);
        }
        $stats['seo_config']++;
    }
} else {
    logMsg('info', 'Tabla ' . v1('seo_config') . ' no encontrada. Omitido.');
}

// ══════════════════════════════════════════════════════════════
// PASO 16: MIGRAR REDIRECTS SEO
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 16: Migrar redirects SEO</h2>';

if (v1TableExists($pdo, 'seo_redirects', $V1_SUFFIX)) {
    $v1Redirects = $pdo->query("SELECT * FROM `" . v1('seo_redirects') . "`")->fetchAll();
    logMsg('info', 'Redirects v1 encontrados: ' . count($v1Redirects));

    foreach ($v1Redirects as $r) {
        if ($DRY_RUN) {
            logMsg('info', "[DRY] Migraría redirect: {$r['url_origen']} → {$r['url_destino']}");
        } else {
            $pdo->prepare("INSERT INTO seo_redirects (url_origen, url_destino, tipo, activo, hits) VALUES (?, ?, ?, ?, ?)")
                ->execute([
                    $r['url_origen'],
                    $r['url_destino'],
                    $r['tipo'] ?? 301,
                    $r['activo'] ?? 1,
                    $r['hits'] ?? 0,
                ]);
        }
        $stats['seo_redirects']++;
    }
} else {
    logMsg('info', 'Tabla ' . v1('seo_redirects') . ' no encontrada.');
}

// ══════════════════════════════════════════════════════════════
// PASO 17: MIGRAR ANALYTICS DIARIO
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 17: Migrar analytics diario</h2>';

if (v1TableExists($pdo, 'analytics_diario', $V1_SUFFIX)) {
    $count = (int) $pdo->query("SELECT COUNT(*) FROM `" . v1('analytics_diario') . "`")->fetchColumn();
    logMsg('info', "Analytics diario v1: {$count} registros");

    if ($count > 0) {
        if ($DRY_RUN) {
            logMsg('info', "[DRY] Migraría {$count} registros de analytics_diario");
        } else {
            $pdo->exec("INSERT IGNORE INTO analytics_diario (fecha, pagina, visitas, visitantes_unicos)
                SELECT fecha, pagina, visitas, visitantes_unicos FROM `" . v1('analytics_diario') . "`");
            logMsg('ok', "Analytics diario migrado: {$count} registros");
        }
        $stats['analytics_diario'] = $count;
    }
} else {
    logMsg('info', 'Tabla ' . v1('analytics_diario') . ' no encontrada. Analytics empieza de cero.');
}

// ══════════════════════════════════════════════════════════════
// PASO 18: MIGRAR CONFIGURACIÓN GENERAL
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 18: Migrar configuración general</h2>';

if (v1TableExists($pdo, 'configuracion', $V1_SUFFIX)) {
    $v1Config = $pdo->query("SELECT * FROM `" . v1('configuracion') . "`")->fetchAll();
    logMsg('info', 'Configuración v1: ' . count($v1Config) . ' registros');

    foreach ($v1Config as $cfg) {
        if ($DRY_RUN) {
            logMsg('info', "[DRY] Migraría config: {$cfg['clave']}");
        } else {
            $pdo->prepare("INSERT INTO configuracion (clave, valor, grupo) VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor)")
                ->execute([$cfg['clave'], $cfg['valor'], $cfg['grupo'] ?? 'general']);
        }
        $stats['configuracion']++;
    }
} else {
    logMsg('info', 'Tabla ' . v1('configuracion') . ' no encontrada. Se usará configuración por defecto.');
}

// ══════════════════════════════════════════════════════════════
// PASO 19: INSERTAR REDIRECTS V1 → V2
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 19: Crear redirects de URLs v1 → v2</h2>';

// Redirects para URLs .php de v1 que cambian a URLs limpias en v2
$v1Redirects = [
    // Páginas estáticas
    ['/buscar.php',     '/buscar'],
    ['/mapa.php',       '/mapa'],
    ['/terminos.php',   '/terminos'],
    ['/privacidad.php', '/privacidad'],
    ['/cookies.php',    '/cookies'],
];

// Agregar redirects dinámicos para comercios con slug
if (!empty($comercioMapV1toV2) || $DRY_RUN) {
    $comercioSlugs = [];
    if (!$DRY_RUN) {
        $comercioSlugs = $pdo->query("SELECT slug FROM comercios")->fetchAll(PDO::FETCH_COLUMN);
    } elseif (v1TableExists($pdo, 'comercios', $V1_SUFFIX)) {
        $comercioSlugs = $pdo->query("SELECT slug FROM `" . v1('comercios') . "`")->fetchAll(PDO::FETCH_COLUMN);
    }
    foreach ($comercioSlugs as $slug) {
        $v1Redirects[] = ["/comercio.php?slug={$slug}", "/comercio/{$slug}"];
    }
}

// Redirects para categorías
$catSlugs = [];
if (!$DRY_RUN) {
    $catSlugs = $pdo->query("SELECT slug FROM categorias")->fetchAll(PDO::FETCH_COLUMN);
} elseif (v1TableExists($pdo, 'categorias', $V1_SUFFIX)) {
    $catSlugs = $pdo->query("SELECT slug FROM `" . v1('categorias') . "`")->fetchAll(PDO::FETCH_COLUMN);
}
foreach ($catSlugs as $slug) {
    $v1Redirects[] = ["/categoria.php?slug={$slug}", "/categoria/{$slug}"];
}

// Redirects para fechas especiales
$fechaSlugs = [];
if (!$DRY_RUN) {
    $fechaSlugs = $pdo->query("SELECT slug FROM fechas_especiales")->fetchAll(PDO::FETCH_COLUMN);
} elseif (v1TableExists($pdo, 'fechas_especiales', $V1_SUFFIX)) {
    $fechaSlugs = $pdo->query("SELECT slug FROM `" . v1('fechas_especiales') . "`")->fetchAll(PDO::FETCH_COLUMN);
}
foreach ($fechaSlugs as $slug) {
    $v1Redirects[] = ["/fecha.php?slug={$slug}", "/fecha/{$slug}"];
}

// Redirects para noticias
$noticiaSlugs = [];
if (!$DRY_RUN) {
    $noticiaSlugs = $pdo->query("SELECT slug FROM noticias")->fetchAll(PDO::FETCH_COLUMN);
} elseif (v1TableExists($pdo, 'noticias', $V1_SUFFIX)) {
    $noticiaSlugs = $pdo->query("SELECT slug FROM `" . v1('noticias') . "`")->fetchAll(PDO::FETCH_COLUMN);
}
foreach ($noticiaSlugs as $slug) {
    $v1Redirects[] = ["/noticia.php?slug={$slug}", "/noticia/{$slug}"];
}

// Redirects para slugs de fechas que cambiaron entre v1 y v2
$slugChanges = [
    '/fecha/pascua'                => '/fecha/pascua-de-resurreccion',
    '/fecha/aniversario-matrimonio'=> '/fecha/aniversario-de-matrimonio',
    '/fecha/cumple-mes-pololeo'    => '/fecha/cumple-mes-de-pololeo',
    '/fecha/bodas-matrimonios'     => '/fecha/bodas-y-matrimonios',
    '/fecha/despedida-soltero'     => '/fecha/despedida-de-solteroa',
];
foreach ($slugChanges as $from => $to) {
    $v1Redirects[] = [$from, $to];
}

$redirectCount = 0;
foreach ($v1Redirects as [$from, $to]) {
    if ($DRY_RUN) {
        if ($redirectCount < 10) {
            logMsg('info', "[DRY] Redirect: {$from} → {$to}");
        }
    } else {
        $pdo->prepare("INSERT IGNORE INTO seo_redirects (url_origen, url_destino, tipo, activo) VALUES (?, ?, 301, 1)")
            ->execute([$from, $to]);
    }
    $redirectCount++;
}
logMsg('ok', "Redirects 301 creados: {$redirectCount}");
if ($DRY_RUN && $redirectCount > 10) {
    logMsg('info', '... y ' . ($redirectCount - 10) . ' redirects más');
}

// ══════════════════════════════════════════════════════════════
// PASO 20: INSERTAR CONFIGURACIÓN MANTENIMIENTO
// ══════════════════════════════════════════════════════════════

echo '<h2>Paso 20: Configuración de mantenimiento</h2>';

if (!$DRY_RUN) {
    $pdo->exec("INSERT IGNORE INTO configuracion_mantenimiento (clave, valor) VALUES
        ('activo', '0'),
        ('mensaje', 'Estamos realizando mejoras en el sitio. Volvemos pronto.'),
        ('ip_permitidas', '')");
    logMsg('ok', 'Configuración de mantenimiento inicializada');
} else {
    logMsg('info', '[DRY] Inicializaría configuración de mantenimiento');
}

// ══════════════════════════════════════════════════════════════
// RESUMEN FINAL
// ══════════════════════════════════════════════════════════════

echo '<div class="summary">';
echo '<h3>' . ($DRY_RUN ? 'Resumen DRY RUN (nada fue modificado)' : 'Migración Completada') . '</h3>';
echo '<ul>';
echo "<li><span class='count'>{$stats['admin_usuarios']}</span> usuarios admin</li>";
echo "<li><span class='count'>{$stats['categorias']}</span> categorías</li>";
echo "<li><span class='count'>{$stats['fechas_especiales']}</span> fechas especiales</li>";
echo "<li><span class='count'>{$stats['comercios']}</span> comercios</li>";
echo "<li><span class='count'>{$stats['comercio_categoria']}</span> relaciones comercio-categoría</li>";
echo "<li><span class='count'>{$stats['comercio_fecha']}</span> relaciones comercio-fecha</li>";
echo "<li><span class='count'>{$stats['comercio_fotos']}</span> fotos de comercios</li>";
echo "<li><span class='count'>{$stats['comercio_horarios']}</span> horarios</li>";
echo "<li><span class='count'>{$stats['noticias']}</span> noticias</li>";
echo "<li><span class='count'>{$stats['noticia_categoria']}</span> relaciones noticia-categoría</li>";
echo "<li><span class='count'>{$stats['noticia_fecha']}</span> relaciones noticia-fecha</li>";
echo "<li><span class='count'>{$stats['banners']}</span> banners</li>";
echo "<li><span class='count'>{$stats['resenas']}</span> reseñas</li>";
echo "<li><span class='count'>{$stats['seo_config']}</span> configuraciones SEO</li>";
echo "<li><span class='count'>{$stats['seo_redirects']}</span> redirects existentes</li>";
echo "<li><span class='count'>{$redirectCount}</span> redirects v1→v2 creados</li>";
echo "<li><span class='count'>{$stats['analytics_diario']}</span> registros analytics</li>";
echo "<li><span class='count'>{$stats['configuracion']}</span> configuraciones generales</li>";
echo "<li><span class='count'>{$stats['imagenes_copiadas']}</span> imágenes copiadas/verificadas</li>";
if ($stats['imagenes_faltantes'] > 0) {
    echo "<li><span class='err'>{$stats['imagenes_faltantes']}</span> imágenes no encontradas en disco</li>";
}
echo '</ul>';

if ($DRY_RUN) {
    echo '<p class="warn" style="font-size:1.1em;margin-top:15px">';
    echo '⚠ Este fue un DRY RUN. No se modificó nada en la base de datos.<br>';
    echo 'Para ejecutar la migración real, use: <code>?key=' . $MIGRATION_KEY . '&mode=run</code>';
    echo '</p>';
} else {
    echo '<p class="ok" style="font-size:1.1em;margin-top:15px">';
    echo '✓ Migración completada exitosamente.<br>';
    echo '<strong style="color:#f87171">⚠ ELIMINE este archivo (migrate_v1_to_v2.php) del servidor AHORA.</strong>';
    echo '</p>';
}

echo '</div>';

// Tiempo de ejecución
$endTime = microtime(true);
echo '<p class="info" style="margin-top:15px">Tiempo de ejecución: ' . round(($endTime - $_SERVER['REQUEST_TIME_FLOAT']) ?? 0, 2) . 's</p>';

echo '</body></html>';
