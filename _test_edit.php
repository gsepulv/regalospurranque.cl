<?php
/**
 * Diagnóstico COMPLETO para error en editar comercios
 * Acceder via: https://regalospurranque.cl/_test_edit.php
 * ELIMINAR después de diagnosticar
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
header('Content-Type: text/plain; charset=utf-8');

// Bootstrap idéntico a index.php
define('BASE_PATH', __DIR__);
require BASE_PATH . '/config/app.php';
require BASE_PATH . '/config/database.php';
if (file_exists(BASE_PATH . '/config/captcha.php')) {
    require BASE_PATH . '/config/captcha.php';
}
if (file_exists(BASE_PATH . '/config/backup.php')) {
    require BASE_PATH . '/config/backup.php';
}

spl_autoload_register(function (string $class): void {
    $path = BASE_PATH . '/' . str_replace('\\', '/', $class) . '.php';
    $path = str_replace('/App/', '/app/', $path);
    if (file_exists($path)) require_once $path;
});
require_once BASE_PATH . '/app/helpers.php';

session_start();

echo "=== DIAGNÓSTICO EDITAR COMERCIOS ===\n";
echo "PHP: " . PHP_VERSION . "\n";
echo "Entorno: " . APP_ENV . "\n";
echo "DB: " . DB_NAME . "\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

$step = 0;
$comercioId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Paso 1: Conexión DB
    echo ++$step . ". Conexión DB... ";
    $db = \App\Core\Database::getInstance();
    echo "OK\n";

    // Paso 2: Buscar un comercio real
    if (!$comercioId) {
        $first = $db->fetch("SELECT id FROM comercios LIMIT 1");
        $comercioId = $first ? (int)$first['id'] : 0;
    }
    echo ++$step . ". Comercio ID=$comercioId... ";
    if (!$comercioId) {
        echo "NO HAY COMERCIOS EN LA BD\n";
        exit;
    }
    $comercio = \App\Models\Comercio::find($comercioId);
    if (!$comercio) {
        echo "NOT FOUND\n";
        exit;
    }
    echo "OK: " . $comercio['nombre'] . "\n";
    echo "   Columnas: " . implode(', ', array_keys($comercio)) . "\n\n";

    // Paso 3: Categorías
    echo ++$step . ". Categoria::getActiveForSelect()... ";
    $categorias = \App\Models\Categoria::getActiveForSelect();
    echo "OK: " . count($categorias) . "\n";

    // Paso 4: Fechas
    echo ++$step . ". FechaEspecial::getActiveForSelect()... ";
    $fechas = \App\Models\FechaEspecial::getActiveForSelect();
    echo "OK: " . count($fechas) . "\n";

    // Paso 5: Categorías del comercio
    echo ++$step . ". Comercio::getCategoriaIds($comercioId)... ";
    $comercioCats = \App\Models\Comercio::getCategoriaIds($comercioId);
    $catIds = array_column($comercioCats, 'categoria_id');
    $catPrincipal = 0;
    foreach ($comercioCats as $cc) {
        if ($cc['es_principal']) $catPrincipal = (int)$cc['categoria_id'];
    }
    echo "OK: " . count($catIds) . " cats, principal=$catPrincipal\n";

    // Paso 6: Fechas del comercio
    echo ++$step . ". Comercio::getFechaIds($comercioId)... ";
    $comercioFechas = \App\Models\Comercio::getFechaIds($comercioId);
    $fechaIds = array_column($comercioFechas, 'fecha_id');
    $fechaData = [];
    foreach ($comercioFechas as $cf) {
        $fechaData[$cf['fecha_id']] = $cf;
    }
    echo "OK: " . count($fechaIds) . " fechas\n";

    // Paso 7: Query planes_config (línea 517 del form)
    echo ++$step . ". planes_config query... ";
    $planes = $db->fetchAll("SELECT slug, nombre, icono FROM planes_config WHERE activo = 1 ORDER BY orden ASC");
    echo "OK: " . count($planes) . " planes\n";

    // Paso 8: AdminUsuario::getComerciantes (línea 558 del form)
    echo ++$step . ". AdminUsuario::getComerciantes()... ";
    $comerciantes = \App\Models\AdminUsuario::getComerciantes();
    echo "OK: " . count($comerciantes) . "\n";

    // Paso 9: Sidebar counts (tabla comercio_renovaciones)
    echo ++$step . ". Sidebar: reseñas pendientes... ";
    try {
        $c = $db->count('resenas', "estado = 'pendiente'");
        echo "OK: $c\n";
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }

    echo ++$step . ". Sidebar: cambios pendientes... ";
    try {
        $c = $db->count('comercio_cambios_pendientes', "estado = 'pendiente'");
        echo "OK: $c\n";
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }

    echo ++$step . ". Sidebar: renovaciones pendientes... ";
    try {
        $c = $db->count('comercio_renovaciones', "estado = 'pendiente'");
        echo "OK: $c\n";
    } catch (\Throwable $e) {
        echo "CAUGHT: " . $e->getMessage() . "\n";
    }

    echo ++$step . ". Sidebar: mensajes no leídos... ";
    try {
        $c = $db->count('mensajes_contacto', 'leido = 0');
        echo "OK: $c\n";
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }

    echo ++$step . ". Sidebar: total comercios... ";
    try {
        $c = $db->count('comercios');
        echo "OK: $c\n";
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }

    // Paso 10: SiteManager (topbar)
    echo ++$step . ". SiteManager::getInstance()... ";
    try {
        $sm = \App\Services\SiteManager::getInstance();
        echo "OK\n";
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }

    // Paso 11: Permission (sidebar)
    echo ++$step . ". Permission service... ";
    try {
        $perm = new \App\Services\Permission();
        echo "OK\n";
    } catch (\Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }

    // === PASO CLAVE: Renderizar form.php ===
    echo "\n=== RENDERIZADO DEL FORMULARIO ===\n";
    echo ++$step . ". Renderizar form.php (sin layout)... ";

    $editing = true;
    $errors = [];
    $admin = ['id' => 1, 'nombre' => 'Test', 'rol' => 'admin'];
    $csrf = 'test_csrf';
    $flash = [];
    $title = 'Test Editar';

    ob_start();
    try {
        include BASE_PATH . '/views/admin/comercios/form.php';
        $content = ob_get_clean();
        echo "OK: " . strlen($content) . " bytes\n";
    } catch (\Throwable $e) {
        $partial = ob_get_clean();
        echo "ERROR en línea " . $e->getLine() . " de " . basename($e->getFile()) . ":\n";
        echo "   " . $e->getMessage() . "\n";
        echo "   Trace: " . $e->getTraceAsString() . "\n";
        echo "   Bytes parciales renderizados: " . strlen($partial) . "\n";
    }

    // === PASO CLAVE: Renderizar sidebar.php ===
    echo ++$step . ". Renderizar sidebar.php... ";
    ob_start();
    try {
        include BASE_PATH . '/views/partials/sidebar.php';
        $sidebarHtml = ob_get_clean();
        echo "OK: " . strlen($sidebarHtml) . " bytes\n";
    } catch (\Throwable $e) {
        $partial = ob_get_clean();
        echo "ERROR en línea " . $e->getLine() . " de " . basename($e->getFile()) . ":\n";
        echo "   " . $e->getMessage() . "\n";
        echo "   Bytes parciales: " . strlen($partial) . "\n";
    }

    // === PASO CLAVE: Renderizar topbar.php ===
    echo ++$step . ". Renderizar topbar.php... ";
    ob_start();
    try {
        include BASE_PATH . '/views/partials/topbar.php';
        $topbarHtml = ob_get_clean();
        echo "OK: " . strlen($topbarHtml) . " bytes\n";
    } catch (\Throwable $e) {
        $partial = ob_get_clean();
        echo "ERROR en línea " . $e->getLine() . " de " . basename($e->getFile()) . ":\n";
        echo "   " . $e->getMessage() . "\n";
        echo "   Bytes parciales: " . strlen($partial) . "\n";
    }

    // === PASO FINAL: Renderizar layout completo ===
    echo ++$step . ". Renderizar layout COMPLETO (form + sidebar + topbar)... ";
    // Primero capturar el form
    ob_start();
    try {
        include BASE_PATH . '/views/admin/comercios/form.php';
        $content = ob_get_clean();
    } catch (\Throwable $e) {
        $content = ob_get_clean();
        echo "ERROR en form: " . $e->getMessage() . "\n";
    }

    // Luego renderizar el layout
    ob_start();
    try {
        include BASE_PATH . '/views/layouts/admin.php';
        $fullPage = ob_get_clean();
        echo "OK: " . strlen($fullPage) . " bytes\n";
    } catch (\Throwable $e) {
        $partial = ob_get_clean();
        echo "ERROR en línea " . $e->getLine() . " de " . basename($e->getFile()) . ":\n";
        echo "   " . $e->getMessage() . "\n";
        echo "   Bytes parciales del layout: " . strlen($partial) . "\n";
    }

    echo "\n=== TODOS LOS TESTS COMPLETADOS ===\n";

} catch (\Throwable $e) {
    echo "\n\nERROR FATAL en paso $step:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}

// Verificar tablas relevantes
echo "\n=== VERIFICACIÓN DE TABLAS ===\n";
try {
    $tables = $db->fetchAll("SHOW TABLES");
    $tableNames = array_map(fn($t) => array_values($t)[0], $tables);
    $check = ['comercios', 'categorias', 'fechas_especiales', 'planes_config',
              'admin_usuarios', 'resenas', 'comercio_cambios_pendientes',
              'comercio_renovaciones', 'mensajes_contacto', 'configuracion'];
    foreach ($check as $t) {
        $exists = in_array($t, $tableNames) ? 'EXISTS' : 'MISSING';
        echo "  $t: $exists\n";
    }
} catch (\Throwable $e) {
    echo "Error verificando tablas: " . $e->getMessage() . "\n";
}

// Verificar columnas de comercios
echo "\n=== COLUMNAS DE 'comercios' ===\n";
try {
    $cols = $db->fetchAll("SHOW COLUMNS FROM comercios");
    foreach ($cols as $col) {
        echo "  {$col['Field']} ({$col['Type']})\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
