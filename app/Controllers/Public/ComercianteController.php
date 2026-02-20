<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\AdminUsuario;
use App\Models\CambioPendiente;
use App\Models\Categoria;
use App\Models\Comercio;
use App\Models\FechaEspecial;
use App\Models\PlanConfig;
use App\Services\Captcha;
use App\Services\FileManager;

/**
 * Panel del Comerciante
 * Login propio, dashboard, editar comercio (cambios pendientes de aprobación)
 * 
 * Diseñado para crecer: v1 = ver + editar básico
 * Futuro: estadísticas, mensajes, promociones, upgrade plan
 */
class ComercianteController extends Controller
{
    // ══════════════════════════════════════════════════════════
    // AUTENTICACIÓN DEL COMERCIANTE
    // ══════════════════════════════════════════════════════════

    /**
     * Formulario de login
     */
    public function loginForm(): void
    {
        if ($this->isLogueado()) {
            header('Location: ' . url('/mi-comercio'));
            exit;
        }

        $this->render('comerciante/login', [
            'title'   => 'Acceder a mi comercio — ' . SITE_NAME,
            'noindex' => true,
        ]);
    }

    /**
     * Procesar login
     */
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $ip       = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        if (empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Ingresa tu email y contraseña.';
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        // Protección fuerza bruta: máx 5 intentos fallidos en 15 min
        try {
            $db = \App\Core\Database::getInstance();
            $intentos = $db->fetch(
                "SELECT COUNT(*) as total FROM login_intentos
                 WHERE ip = ? AND exitoso = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)",
                [$ip]
            );
            if ($intentos && (int)$intentos['total'] >= 5) {
                $_SESSION['flash_error'] = 'Demasiados intentos fallidos. Intenta en 15 minutos.';
                $_SESSION['flash_old'] = ['email' => $email];
                header('Location: ' . url('/mi-comercio/login'));
                exit;
            }
        } catch (\Throwable $e) {}

        // Validar Turnstile
        if (!Captcha::verify($_POST['cf-turnstile-response'] ?? null)) {
            $_SESSION['flash_error'] = 'Verificación anti-bot fallida. Intenta nuevamente.';
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        // Buscar usuario comerciante
        $user = AdminUsuario::findByEmailAndRol($email, 'comerciante');

        if (!$user || !password_verify($password, $user['password_hash'])) {
            try {
                $db = $db ?? \App\Core\Database::getInstance();
                $db->insert('login_intentos', ['ip' => $ip, 'email' => $email, 'exitoso' => 0]);
            } catch (\Throwable $e) {}
            $_SESSION['flash_error'] = 'Credenciales incorrectas.';
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        if (!$user['activo']) {
            $_SESSION['flash_error'] = 'Tu cuenta aún está pendiente de aprobación. Te notificaremos cuando esté activa.';
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        // Prevenir session fixation
        session_regenerate_id(true);

        // Crear sesión del comerciante (separada del admin)
        $_SESSION['comerciante'] = [
            'id'     => (int)$user['id'],
            'nombre' => $user['nombre'],
            'email'  => $user['email'],
        ];

        // Registrar intento exitoso
        try {
            $db = $db ?? \App\Core\Database::getInstance();
            $db->insert('login_intentos', ['ip' => $ip, 'email' => $email, 'exitoso' => 1]);
        } catch (\Throwable $e) {}

        // Actualizar último login
        AdminUsuario::updateById($user['id'], ['last_login' => date('Y-m-d H:i:s')]);

        header('Location: ' . url('/mi-comercio'));
        exit;
    }

    /**
     * Cerrar sesión
     */
    public function logout(): void
    {
        unset($_SESSION['comerciante']);
        $_SESSION['flash_success'] = 'Has cerrado sesión correctamente.';
        header('Location: ' . url('/mi-comercio/login'));
        exit;
    }

    // ══════════════════════════════════════════════════════════
    // DASHBOARD
    // ══════════════════════════════════════════════════════════

    /**
     * Dashboard principal del comerciante
     */
    public function dashboard(): void
    {
        if (!$this->isLogueado()) {
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        $uid = $_SESSION['comerciante']['id'];

        // Obtener comercio del usuario
        $comercio = Comercio::findByRegistradoPorWithCategorias($uid);

        // Verificar si tiene cambios pendientes
        $pendientes = null;
        if ($comercio) {
            $pendientes = CambioPendiente::getLatestPendiente($comercio['id']);
        }

        // Datos del plan
        $plan = null;
        if ($comercio) {
            $plan = PlanConfig::findBySlug($comercio['plan']);
        }

        $this->render('comerciante/dashboard', [
            'title'      => 'Mi comercio — ' . SITE_NAME,
            'noindex'    => true,
            'comercio'   => $comercio,
            'pendientes' => $pendientes,
            'plan'       => $plan,
            'usuario'    => $_SESSION['comerciante'],
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // EDITAR COMERCIO
    // ══════════════════════════════════════════════════════════

    /**
     * Formulario de edición
     */
    public function editar(): void
    {
        if (!$this->isLogueado()) {
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        $uid = $_SESSION['comerciante']['id'];
        $comercio = Comercio::findByRegistradoPor($uid);

        if (!$comercio) {
            $_SESSION['flash_error'] = 'No se encontró tu comercio.';
            header('Location: ' . url('/mi-comercio'));
            exit;
        }

        // Categorías y fechas
        $categorias = Categoria::getActiveForSelect();
        $fechas = FechaEspecial::getActiveForSelect();

        // Categorías actuales del comercio
        $catActuales = Comercio::getCategoriaIds($comercio['id']);
        $catIds = array_column($catActuales, 'categoria_id');
        $catPrincipal = 0;
        foreach ($catActuales as $ca) {
            if ($ca['es_principal']) { $catPrincipal = $ca['categoria_id']; break; }
        }

        // Fechas actuales
        $fechaActuales = Comercio::getFechaIds($comercio['id']);
        $fechaIds = array_column($fechaActuales, 'fecha_id');

        // Plan actual para límites
        $plan = PlanConfig::findBySlug($comercio['plan']);

        // Determinar cuál red social tiene (para freemium con 1 sola)
        $redActual = ['tipo' => '', 'url' => ''];
        $redesCampos = ['facebook','instagram','tiktok','youtube','x_twitter','linkedin','telegram','pinterest'];
        foreach ($redesCampos as $red) {
            if (!empty($comercio[$red])) {
                $redActual = ['tipo' => $red, 'url' => $comercio[$red]];
                break;
            }
        }

        $this->render('comerciante/editar', [
            'title'         => 'Editar mi comercio — ' . SITE_NAME,
            'noindex'       => true,
            'comercio'      => $comercio,
            'categorias'    => $categorias,
            'fechas'        => $fechas,
            'catIds'        => $catIds,
            'catPrincipal'  => $catPrincipal,
            'fechaIds'      => $fechaIds,
            'plan'          => $plan,
            'redActual'     => $redActual,
            'usuario'       => $_SESSION['comerciante'],
        ]);
    }

    /**
     * Guardar cambios (quedan pendientes de aprobación)
     */
    public function guardar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->isLogueado()) {
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        $uid = $_SESSION['comerciante']['id'];
        $comercio = Comercio::findByRegistradoPor($uid);

        if (!$comercio) {
            header('Location: ' . url('/mi-comercio'));
            exit;
        }

        // Recopilar todos los cambios
        $cambios = [];

        // Campos de texto
        $camposTexto = ['nombre','descripcion','whatsapp','telefono','email','sitio_web','direccion'];
        foreach ($camposTexto as $campo) {
            $nuevo = trim($_POST[$campo] ?? '');
            $actual = $comercio[$campo] ?? '';
            if ($nuevo !== $actual) {
                $cambios[$campo] = ['anterior' => $actual, 'nuevo' => $nuevo];
            }
        }

        // Coordenadas
        foreach (['lat','lng'] as $coord) {
            $nuevo = !empty($_POST[$coord]) ? (float)$_POST[$coord] : null;
            $actual = $comercio[$coord] ? (float)$comercio[$coord] : null;
            if ($nuevo !== $actual) {
                $cambios[$coord] = ['anterior' => $actual, 'nuevo' => $nuevo];
            }
        }

        // Red social (plan freemium = 1 sola)
        $redTipo = $_POST['red_social_tipo'] ?? '';
        $redUrl  = trim($_POST['red_social_url'] ?? '');
        $redesCampos = ['facebook','instagram','tiktok','youtube','x_twitter','linkedin','telegram','pinterest'];

        // Limpiar todas y poner solo la elegida
        foreach ($redesCampos as $red) {
            $nuevoVal = ($red === $redTipo && !empty($redUrl)) ? $redUrl : null;
            $actualVal = $comercio[$red] ?? null;
            if ($nuevoVal !== $actualVal) {
                $cambios[$red] = ['anterior' => $actualVal, 'nuevo' => $nuevoVal];
            }
        }

        // Imágenes nuevas
        if (!empty($_FILES['logo']['tmp_name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $nuevoLogo = FileManager::subirImagen($_FILES['logo'], 'logos', 800) ?: null;
            if ($nuevoLogo) {
                $cambios['logo'] = ['anterior' => $comercio['logo'], 'nuevo' => $nuevoLogo];
            }
        }
        if (!empty($_FILES['portada']['tmp_name']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
            $nuevaPortada = FileManager::subirImagen($_FILES['portada'], 'portadas', 1200) ?: null;
            if ($nuevaPortada) {
                $cambios['portada'] = ['anterior' => $comercio['portada'], 'nuevo' => $nuevaPortada];
            }
        }

        // Categorías
        $nuevasCats = array_map('intval', $_POST['categorias'] ?? []);
        $catRows = Comercio::getCategoriaIds($comercio['id']);
        $actualesCats = array_map('intval', array_column($catRows, 'categoria_id'));
        sort($nuevasCats);
        sort($actualesCats);
        if ($nuevasCats !== $actualesCats) {
            $cambios['categorias'] = [
                'anterior' => $actualesCats,
                'nuevo'    => $nuevasCats,
                'principal' => (int)($_POST['categoria_principal'] ?? 0),
            ];
        }

        // Fechas
        $nuevasFechas = array_map('intval', $_POST['fechas'] ?? []);
        $fechaRows = Comercio::getFechaIds($comercio['id']);
        $actualesFechas = array_map('intval', array_column($fechaRows, 'fecha_id'));
        sort($nuevasFechas);
        sort($actualesFechas);
        if ($nuevasFechas !== $actualesFechas) {
            $cambios['fechas'] = ['anterior' => $actualesFechas, 'nuevo' => $nuevasFechas];
        }

        if (empty($cambios)) {
            $_SESSION['flash_info'] = 'No detectamos cambios en tu información.';
            header('Location: ' . url('/mi-comercio'));
            exit;
        }

        // Guardar cambios pendientes
        CambioPendiente::create([
            'comercio_id'  => $comercio['id'],
            'usuario_id'   => $uid,
            'cambios_json' => json_encode($cambios, JSON_UNESCAPED_UNICODE),
            'estado'       => 'pendiente',
            'notas'        => '',
        ]);

        // Notificar admin
        $this->notificarCambios($comercio['id'], $comercio['nombre']);

        $_SESSION['flash_success'] = '¡Cambios enviados! Serán revisados por nuestro equipo antes de publicarse.';
        header('Location: ' . url('/mi-comercio'));
        exit;
    }

    // ══════════════════════════════════════════════════════════
    // HELPERS
    // ══════════════════════════════════════════════════════════

    private function isLogueado(): bool
    {
        return !empty($_SESSION['comerciante']['id']);
    }

    private function notificarCambios(int $comercioId, string $nombreComercio): void
    {
        try {
            $adminEmail = AdminUsuario::getFirstAdminEmail();
            if (!$adminEmail) return;

            $asunto = "✏️ Cambios pendientes: {$nombreComercio}";
            $cuerpo  = "El comercio «{$nombreComercio}» ha enviado cambios para revisión.\n\n";
            $cuerpo .= "Revísalos en: " . SITE_URL . "/admin/comercios/editar/{$comercioId}\n";

            $headers  = "From: " . SITE_NAME . " <noreply@regalospurranque.cl>\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            mail($adminEmail, $asunto, $cuerpo, $headers);
        } catch (\Throwable $e) {}
    }
}
