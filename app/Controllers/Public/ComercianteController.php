<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\AdminUsuario;
use App\Models\CambioPendiente;
use App\Models\Categoria;
use App\Models\Comercio;
use App\Models\Configuracion;
use App\Models\FechaEspecial;
use App\Models\PlanConfig;
use App\Models\Producto;
use App\Models\RenovacionComercio;
use App\Services\Captcha;
use App\Services\FileManager;
use App\Services\Mailer;

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
            $_SESSION['flash_error'] = 'Tu cuenta ha sido desactivada. Contáctanos si necesitas ayuda.';
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
    // RECUPERACIÓN DE CONTRASEÑA
    // ══════════════════════════════════════════════════════════

    /**
     * Formulario "Olvidé mi contraseña"
     */
    public function forgotPasswordForm(): void
    {
        if ($this->isLogueado()) {
            header('Location: ' . url('/mi-comercio'));
            exit;
        }

        $this->render('comerciante/olvide-contrasena', [
            'title'   => 'Recuperar contraseña — ' . SITE_NAME,
            'noindex' => true,
        ]);
    }

    /**
     * Enviar link de recuperación
     */
    public function sendResetLink(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/mi-comercio/olvide-contrasena'));
            exit;
        }

        $email = strtolower(trim($_POST['email'] ?? ''));

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['flash_error'] = 'Ingresa un email válido.';
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: ' . url('/mi-comercio/olvide-contrasena'));
            exit;
        }

        // Validar Turnstile
        if (!Captcha::verify($_POST['cf-turnstile-response'] ?? null)) {
            $_SESSION['flash_error'] = 'Verificación anti-bot fallida. Intenta nuevamente.';
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: ' . url('/mi-comercio/olvide-contrasena'));
            exit;
        }

        // Buscar usuario comerciante
        $user = AdminUsuario::findByEmailAndRol($email, 'comerciante');

        if ($user && $user['activo']) {
            // Anti-flood: no generar nuevo token si ya tiene uno vigente
            if (empty($user['reset_token']) || empty($user['reset_expira']) || strtotime($user['reset_expira']) < time()) {
                $token  = bin2hex(random_bytes(32));
                $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
                AdminUsuario::setResetToken($user['id'], $token, $expira);

                $resetUrl = SITE_URL . '/mi-comercio/reset/' . $token;
                $mailer = new Mailer();
                $mailer->send($user['email'], 'Recuperar contraseña — ' . SITE_NAME, 'reset-password', [
                    'nombre'   => $user['nombre'],
                    'resetUrl' => $resetUrl,
                ]);
            }
        }

        // Siempre mensaje genérico (no revelar si el email existe)
        $_SESSION['flash_success'] = 'Si el email está registrado, recibirás instrucciones para restablecer tu contraseña.';
        header('Location: ' . url('/mi-comercio/olvide-contrasena'));
        exit;
    }

    /**
     * Formulario para establecer nueva contraseña
     */
    public function resetPasswordForm(string $token): void
    {
        $user = AdminUsuario::findByResetToken($token);

        if (!$user) {
            $_SESSION['flash_error'] = 'El enlace es inválido o ha expirado. Solicita uno nuevo.';
            header('Location: ' . url('/mi-comercio/olvide-contrasena'));
            exit;
        }

        $this->render('comerciante/nueva-contrasena', [
            'title'   => 'Nueva contraseña — ' . SITE_NAME,
            'noindex' => true,
            'token'   => $token,
        ]);
    }

    /**
     * Procesar nueva contraseña
     */
    public function resetPassword(string $token): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/mi-comercio/olvide-contrasena'));
            exit;
        }

        $user = AdminUsuario::findByResetToken($token);

        if (!$user) {
            $_SESSION['flash_error'] = 'El enlace es inválido o ha expirado. Solicita uno nuevo.';
            header('Location: ' . url('/mi-comercio/olvide-contrasena'));
            exit;
        }

        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['password_confirm'] ?? '';

        if (mb_strlen($password) < 8) {
            $_SESSION['flash_error'] = 'La contraseña debe tener al menos 8 caracteres.';
            header('Location: ' . url('/mi-comercio/reset/' . $token));
            exit;
        }

        if ($password !== $confirm) {
            $_SESSION['flash_error'] = 'Las contraseñas no coinciden.';
            header('Location: ' . url('/mi-comercio/reset/' . $token));
            exit;
        }

        // Actualizar contraseña y limpiar token
        AdminUsuario::updateById($user['id'], [
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);
        AdminUsuario::clearResetToken($user['id']);

        $_SESSION['flash_success'] = '¡Contraseña actualizada! Ya puedes ingresar con tu nueva contraseña.';
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

        // Si no tiene comercio, redirigir al Paso 2 del registro
        if (!$comercio) {
            $_SESSION['registro_uid']    = $_SESSION['comerciante']['id'];
            $_SESSION['registro_nombre'] = $_SESSION['comerciante']['nombre'];
            $_SESSION['registro_email']  = $_SESSION['comerciante']['email'];
            $_SESSION['flash_info'] = '¡Bienvenido/a! Completa los datos de tu comercio para finalizar el registro.';
            header('Location: ' . url('/registrar-comercio/datos'));
            exit;
        }

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

        // Datos de renovación
        $renovacion = null;
        $renovacionesActivas = false;
        $planesDisponibles = [];
        $metodosPago = [];
        $datosBanco = [];
        if ($comercio) {
            $confRenov = Configuracion::getByKey('renovaciones_activas');
            $renovacionesActivas = $confRenov && $confRenov['valor'] === '1';
            if ($renovacionesActivas) {
                $renovacion = RenovacionComercio::getLatestPendienteByComercio($comercio['id']);
                $planesDisponibles = PlanConfig::getActiveForRenewal();
                $metodosPago = \App\Services\PasarelaPago::getMetodosActivos();
                $pago = new \App\Services\PagoTransferencia();
                $datosBanco = $pago->getDatosBancarios();
            }
        }

        // Estadísticas del comercio
        $estadisticas = [
            'visitas_30d' => 0,
            'visitas_hoy' => 0,
            'whatsapp_30d' => 0,
            'compartidos' => 0,
            'productos_activos' => 0,
            'visitas_7d' => [],
        ];
        if ($comercio) {
            try {
                $db = \App\Core\Database::getInstance();
                $cid = (int) $comercio['id'];

                $estadisticas['visitas_30d'] = (int) ($db->fetch(
                    "SELECT COUNT(*) as t FROM visitas_log WHERE comercio_id = ? AND tipo = 'comercio' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)", [$cid]
                )['t'] ?? 0);

                $estadisticas['visitas_hoy'] = (int) ($db->fetch(
                    "SELECT COUNT(*) as t FROM visitas_log WHERE comercio_id = ? AND tipo = 'comercio' AND DATE(created_at) = CURDATE()", [$cid]
                )['t'] ?? 0);

                $estadisticas['whatsapp_30d'] = (int) ($db->fetch(
                    "SELECT COUNT(*) as t FROM visitas_log WHERE comercio_id = ? AND tipo = 'whatsapp' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)", [$cid]
                )['t'] ?? 0);

                $estadisticas['compartidos'] = (int) ($db->fetch(
                    "SELECT COUNT(*) as t FROM share_log WHERE comercio_id = ?", [$cid]
                )['t'] ?? 0);

                $estadisticas['productos_activos'] = (int) ($db->fetch(
                    "SELECT COUNT(*) as t FROM productos WHERE comercio_id = ? AND activo = 1", [$cid]
                )['t'] ?? 0);

                $estadisticas['visitas_7d'] = $db->fetchAll(
                    "SELECT DATE(created_at) as fecha, COUNT(*) as visitas FROM visitas_log WHERE comercio_id = ? AND tipo = 'comercio' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(created_at) ORDER BY fecha ASC", [$cid]
                );

                $estadisticas['productos_top'] = $db->fetchAll(
                    "SELECT id, nombre, precio, activo FROM productos WHERE comercio_id = ? ORDER BY activo DESC, created_at DESC LIMIT 5", [$cid]
                );
            } catch (\Throwable $e) {}
        }

        $this->render('comerciante/dashboard', [
            'title'               => 'Mi comercio — ' . SITE_NAME,
            'noindex'             => true,
            'comercio'            => $comercio,
            'pendientes'          => $pendientes,
            'plan'                => $plan,
            'usuario'             => $_SESSION['comerciante'],
            'renovacion'          => $renovacion,
            'renovacionesActivas' => $renovacionesActivas,
            'planesDisponibles'   => $planesDisponibles,
            'metodosPago'         => $metodosPago,
            'datosBanco'          => $datosBanco,
            'estadisticas'        => $estadisticas,
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

        // Validar campos antes de procesar
        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $whatsapp    = trim($_POST['whatsapp'] ?? '');
        $telefono    = trim($_POST['telefono'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $sitio_web   = trim($_POST['sitio_web'] ?? '');
        $direccion   = trim($_POST['direccion'] ?? '');

        $errors = [];
        if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 100) {
            $errors[] = 'El nombre debe tener entre 3 y 100 caracteres.';
        }
        if (mb_strlen($descripcion) < 20 || mb_strlen($descripcion) > 5000) {
            $errors[] = 'La descripcion debe tener entre 20 y 5000 caracteres.';
        }
        if (mb_strlen($whatsapp) < 9 || mb_strlen($whatsapp) > 15) {
            $errors[] = 'El WhatsApp debe tener entre 9 y 15 caracteres.';
        }
        if (mb_strlen($telefono) > 0 && (mb_strlen($telefono) < 9 || mb_strlen($telefono) > 15)) {
            $errors[] = 'El teléfono debe tener entre 9 y 15 caracteres.';
        }
        if (mb_strlen($email) > 0 && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Ingresa un email válido.';
        }
        if (mb_strlen($email) > 100) {
            $errors[] = 'El email no puede superar los 100 caracteres.';
        }
        if (!empty($sitio_web) && !filter_var($sitio_web, FILTER_VALIDATE_URL)) {
            $errors[] = 'La URL del sitio web no es válida.';
        }
        if (mb_strlen($sitio_web) > 255) {
            $errors[] = 'El sitio web no puede superar los 255 caracteres.';
        }
        if (mb_strlen($direccion) < 5 || mb_strlen($direccion) > 255) {
            $errors[] = 'La dirección debe tener entre 5 y 255 caracteres.';
        }

        // Validar coordenadas
        if (!empty($_POST['lat'])) {
            $latVal = (float) $_POST['lat'];
            if ($latVal < -90 || $latVal > 90) $errors[] = 'La latitud debe estar entre -90 y 90.';
        }
        if (!empty($_POST['lng'])) {
            $lngVal = (float) $_POST['lng'];
            if ($lngVal < -180 || $lngVal > 180) $errors[] = 'La longitud debe estar entre -180 y 180.';
        }

        // Validar URL red social
        $redUrl = trim($_POST['red_social_url'] ?? '');
        if (!empty($redUrl) && !filter_var($redUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'La URL de la red social no es válida.';
        }

        // Validar tamaño de archivos antes de procesarlos
        $maxBytes = UPLOAD_MAX_SIZE;
        $maxMb = round($maxBytes / 1024 / 1024);
        foreach (['logo', 'portada'] as $campo) {
            if (!empty($_FILES[$campo]['tmp_name']) && $_FILES[$campo]['error'] === UPLOAD_ERR_OK) {
                if ($_FILES[$campo]['size'] > $maxBytes) {
                    $errors[] = "La imagen {$campo} no debe superar {$maxMb} MB.";
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['flash_old'] = $_POST;
            header('Location: ' . url('/mi-comercio/editar'));
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

        // Delivery / Envíos (booleanos)
        foreach (['delivery_local', 'envios_chile'] as $boolCampo) {
            $nuevo = isset($_POST[$boolCampo]) ? 1 : 0;
            $actual = (int) ($comercio[$boolCampo] ?? 0);
            if ($nuevo !== $actual) {
                $cambios[$boolCampo] = ['anterior' => $actual, 'nuevo' => $nuevo];
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
    // RENOVACIÓN DE PLAN
    // ══════════════════════════════════════════════════════════

    /**
     * Procesar solicitud de renovación de plan
     */
    public function solicitarRenovacion(): void
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

        // Verificar que renovaciones están activas
        $confRenov = Configuracion::getByKey('renovaciones_activas');
        if (!$confRenov || $confRenov['valor'] !== '1') {
            $_SESSION['flash_error'] = 'El sistema de renovaciones no está habilitado.';
            header('Location: ' . url('/mi-comercio'));
            exit;
        }

        // Verificar que no tenga solicitud pendiente
        if (RenovacionComercio::hasPendiente($comercio['id'])) {
            $_SESSION['flash_error'] = 'Ya tienes una solicitud de renovación pendiente.';
            header('Location: ' . url('/mi-comercio'));
            exit;
        }

        // Validar plan solicitado
        $planSlug = trim($_POST['plan_solicitado'] ?? '');
        $planSolicitado = PlanConfig::findBySlug($planSlug);
        if (!$planSolicitado || !$planSolicitado['activo'] || $planSlug === 'banner') {
            $_SESSION['flash_error'] = 'Plan no válido.';
            header('Location: ' . url('/mi-comercio'));
            exit;
        }

        // Validar método de pago
        $metodoPago = trim($_POST['metodo_pago'] ?? '');
        $metodosActivos = \App\Services\PasarelaPago::getMetodosActivos();
        if (!in_array($metodoPago, $metodosActivos)) {
            $_SESSION['flash_error'] = 'Método de pago no válido.';
            header('Location: ' . url('/mi-comercio'));
            exit;
        }

        // Subir comprobante si es transferencia
        $comprobante = null;
        if ($metodoPago === 'transferencia' && !empty($_FILES['comprobante']['tmp_name']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
            $comprobante = FileManager::subirComprobante($_FILES['comprobante'], 1200);
            if (!$comprobante) {
                $_SESSION['flash_error'] = 'No se pudo subir el comprobante. Verifica que sea una imagen válida (JPG, PNG, WebP) de máximo 5 MB.';
                header('Location: ' . url('/mi-comercio'));
                exit;
            }
        }

        // Fecha de pago
        $fechaPago = !empty($_POST['fecha_pago']) ? $_POST['fecha_pago'] : null;
        if ($fechaPago && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaPago)) {
            $fechaPago = null;
        }

        // Monto desde el plan
        $monto = (float)($planSolicitado['precio_regular'] ?? 0);

        // Crear solicitud
        RenovacionComercio::create([
            'comercio_id'      => $comercio['id'],
            'usuario_id'       => $uid,
            'plan_actual'      => $comercio['plan'] ?? 'freemium',
            'plan_solicitado'  => $planSlug,
            'monto'            => $monto > 0 ? $monto : null,
            'comprobante_pago' => $comprobante,
            'fecha_pago'       => $fechaPago,
            'metodo_pago'      => $metodoPago,
        ]);

        // Notificar admins
        try {
            \App\Services\Notification::renovacionNuevaAdmin($comercio, $planSolicitado);
        } catch (\Throwable $e) {}

        $_SESSION['flash_success'] = '¡Solicitud de renovación enviada! Nuestro equipo la revisará pronto.';
        header('Location: ' . url('/mi-comercio'));
        exit;
    }

    // ══════════════════════════════════════════════════════════
    // PERFIL DEL COMERCIANTE
    // ══════════════════════════════════════════════════════════

    /**
     * Página de perfil: datos básicos + cambiar contraseña
     */
    public function perfil(): void
    {
        if (!$this->isLogueado()) {
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        $uid = $_SESSION['comerciante']['id'];
        $usuario = AdminUsuario::find($uid);

        $this->render('comerciante/perfil', [
            'title'   => 'Mi perfil — ' . SITE_NAME,
            'noindex' => true,
            'usuario' => $usuario,
        ]);
    }

    /**
     * Actualizar contraseña del comerciante
     */
    public function updatePassword(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->isLogueado()) {
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        // Validar Turnstile
        if (!Captcha::verify($_POST['cf-turnstile-response'] ?? null)) {
            $_SESSION['flash_error'] = 'Verificación anti-bot fallida. Intenta nuevamente.';
            header('Location: ' . url('/mi-comercio/perfil'));
            exit;
        }

        $uid = $_SESSION['comerciante']['id'];
        $usuario = AdminUsuario::find($uid);

        $actual  = $_POST['password_actual'] ?? '';
        $nueva   = $_POST['password_nueva'] ?? '';
        $confirm = $_POST['password_confirm'] ?? '';

        if (!password_verify($actual, $usuario['password_hash'])) {
            $_SESSION['flash_error'] = 'La contraseña actual es incorrecta.';
            header('Location: ' . url('/mi-comercio/perfil'));
            exit;
        }

        if (mb_strlen($nueva) < 8) {
            $_SESSION['flash_error'] = 'La nueva contraseña debe tener al menos 8 caracteres.';
            header('Location: ' . url('/mi-comercio/perfil'));
            exit;
        }

        if ($nueva !== $confirm) {
            $_SESSION['flash_error'] = 'Las contraseñas nuevas no coinciden.';
            header('Location: ' . url('/mi-comercio/perfil'));
            exit;
        }

        AdminUsuario::updateById($uid, [
            'password_hash' => password_hash($nueva, PASSWORD_DEFAULT),
        ]);

        // Invalidar tokens de reset pendientes
        AdminUsuario::clearResetToken($uid);

        // Regenerar sesión para prevenir session fixation
        session_regenerate_id(true);

        // Registrar evento
        try {
            \App\Services\Logger::log('auth', 'password_change', 'usuario', $uid, 'Cambio de contraseña desde perfil comerciante');
        } catch (\Throwable $e) {}

        $_SESSION['flash_success'] = 'Contraseña actualizada correctamente.';
        header('Location: ' . url('/mi-comercio/perfil'));
        exit;
    }

    /**
     * Actualizar datos básicos de la cuenta (nombre, email, teléfono)
     */
    public function updateDatos(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->isLogueado()) {
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        $uid = $_SESSION['comerciante']['id'];

        $nombre   = trim($_POST['nombre'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $telefono = trim($_POST['telefono'] ?? '');

        $errores = [];
        if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 100) {
            $errores[] = 'El nombre debe tener entre 3 y 100 caracteres.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Ingresa un email válido.';
        }
        if (mb_strlen($email) > 100) {
            $errores[] = 'El email no puede superar los 100 caracteres.';
        }
        if (mb_strlen($telefono) < 9 || mb_strlen($telefono) > 15) {
            $errores[] = 'El teléfono debe tener entre 9 y 15 caracteres.';
        }

        // Verificar email duplicado
        if (empty($errores)) {
            $existe = AdminUsuario::findByEmail($email);
            if ($existe && (int)$existe['id'] !== $uid) {
                $errores[] = 'Ya existe otra cuenta con ese email.';
            }
        }

        if (!empty($errores)) {
            $_SESSION['flash_errors'] = $errores;
            $_SESSION['flash_old'] = ['nombre' => $nombre, 'email' => $email, 'telefono' => $telefono];
            header('Location: ' . url('/mi-comercio/perfil'));
            exit;
        }

        AdminUsuario::updateById($uid, [
            'nombre'   => $nombre,
            'email'    => $email,
            'telefono' => $telefono,
        ]);

        // Regenerar sesión con datos nuevos
        $_SESSION['comerciante']['nombre'] = $nombre;
        $_SESSION['comerciante']['email']  = $email;

        $_SESSION['flash_success'] = 'Datos actualizados correctamente.';
        header('Location: ' . url('/mi-comercio/perfil'));
        exit;
    }

    // ══════════════════════════════════════════════════════════
    // PRODUCTOS DEL COMERCIANTE
    // ══════════════════════════════════════════════════════════

    /**
     * Listar productos del comerciante
     */
    public function productos(): void
    {
        if (!$this->isLogueado()) {
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        $uid = $_SESSION['comerciante']['id'];
        $comercio = Comercio::findByRegistradoPorWithCategorias($uid);
        $comercioId = $comercio['id'] ?? 0;
        if (!$comercio) {
            header('Location: ' . url('/mi-comercio'));
            exit;
        }

        $plan = PlanConfig::findBySlug($comercio['plan'] ?? 'freemium');
        $maxProductos = $plan['max_productos'] ?? 5;

        $this->render('comerciante/productos/index', [
            'title'          => 'Mis productos — ' . SITE_NAME,
            'noindex'        => true,
            'comercio'       => $comercio,
            'productos'      => Producto::findByComercioId($comercioId, false),
            'totalProductos' => Producto::countByComercioId($comercioId),
            'maxProductos'   => $maxProductos,
            'plan'           => $plan,
        ]);
    }

    /**
     * Toggle delivery/envios - guardado directo sin aprobacion
     */
    public function productoDespacho(): void
    {
        if (!$this->isLogueado() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        $uid = $_SESSION['comerciante']['id'];
        $comercio = Comercio::findByRegistradoPorWithCategorias($uid);
        if (!$comercio) {
            header('Location: ' . url('/mi-comercio'));
            exit;
        }

        $deliveryLocal = isset($_POST['delivery_local']) ? 1 : 0;
        $enviosChile = isset($_POST['envios_chile']) ? 1 : 0;

        Comercio::update($comercio['id'], [
            'delivery_local' => $deliveryLocal,
            'envios_chile'   => $enviosChile,
        ]);

        $_SESSION['flash_success'] = 'Opciones de despacho actualizadas.';
        header('Location: ' . url('/mi-comercio/productos'));
        exit;
    }

    /**
     * Formulario de nuevo producto
     */
    public function productoCrear(): void
    {
        if (!$this->isLogueado()) {
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        $uid = $_SESSION['comerciante']['id'];
        $comercio = Comercio::findByRegistradoPorWithCategorias($uid);
        $comercioId = $comercio['id'] ?? 0;
        if (!$comercio) {
            header('Location: ' . url('/mi-comercio'));
            exit;
        }

        $plan = PlanConfig::findBySlug($comercio['plan'] ?? 'freemium');
        $maxProductos = $plan['max_productos'] ?? 5;

        if (Producto::countByComercioId($comercioId) >= $maxProductos) {
            $_SESSION['flash_error'] = 'Has alcanzado el límite de productos de tu plan.';
            header('Location: ' . url('/mi-comercio/productos'));
            exit;
        }

        $this->render('comerciante/productos/form', [
            'title'          => 'Nuevo producto — ' . SITE_NAME,
            'noindex'        => true,
            'comercio'       => $comercio,
            'producto'       => null,
            'totalProductos' => Producto::countByComercioId($comercioId),
            'maxProductos'   => $maxProductos,
            'plan'           => $plan,
        ]);
    }

    /**
     * Guardar nuevo producto (POST)
     */
    public function productoGuardar(): void
    {
        if (!$this->isLogueado() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        $uid = $_SESSION['comerciante']['id'];
        $comercio = Comercio::findByRegistradoPorWithCategorias($uid);
        $comercioId = $comercio['id'] ?? 0;
        if (!$comercio) {
            header('Location: ' . url('/mi-comercio'));
            exit;
        }

        $plan = PlanConfig::findBySlug($comercio['plan'] ?? 'freemium');
        $maxProductos = $plan['max_productos'] ?? 5;

        if (Producto::countByComercioId($comercioId) >= $maxProductos) {
            $_SESSION['flash_error'] = 'Has alcanzado el límite de productos de tu plan.';
            header('Location: ' . url('/mi-comercio/productos'));
            exit;
        }

        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $descripcion_detallada = trim($_POST['descripcion_detallada'] ?? '');
        $precio      = $_POST['precio'] ?? null;
        $activo      = isset($_POST['activo']) ? 1 : 0;
        $tipo        = $_POST['tipo'] ?? 'producto';
        $estado      = $_POST['estado'] ?? 'disponible';
        $stock       = $_POST['stock'] ?? null;
        $condicion   = $_POST['condicion'] ?? null;

        // Validar enums
        $tiposValidos = ['producto', 'servicio', 'inmueble'];
        $estadosValidos = ['disponible', 'vendido', 'reservado', 'agotado'];
        $condicionesValidas = ['nuevo', 'usado', 'reacondicionado', ''];
        if (!in_array($tipo, $tiposValidos)) $tipo = 'producto';
        if (!in_array($estado, $estadosValidos)) $estado = 'disponible';
        if (!in_array($condicion ?? '', $condicionesValidas)) $condicion = null;
        if ($condicion === '') $condicion = null;
        if ($stock !== null && $stock !== '') { $stock = (int) $stock; if ($stock < 0) $stock = 0; } else { $stock = null; }
        if (mb_strlen($descripcion_detallada) > 2000) $descripcion_detallada = mb_substr($descripcion_detallada, 0, 2000);

        $errores = [];
        if (empty($nombre)) {
            $errores[] = 'El nombre del producto es obligatorio.';
        } elseif (mb_strlen($nombre) > 150) {
            $errores[] = 'El nombre no puede superar los 150 caracteres.';
        }
        if (mb_strlen($descripcion) > 500) {
            $errores[] = 'La descripción no puede superar los 500 caracteres.';
        }
        if ($precio !== null && $precio !== '') {
            $precio = (int) $precio;
            if ($precio < 0) {
                $errores[] = 'El precio debe ser mayor o igual a 0.';
            }
        } else {
            $precio = null;
        }


        // Campos especificos por tipo
        $modalidad = $_POST['modalidad'] ?? null;
        $horario_atencion = trim($_POST['horario_atencion'] ?? '');
        $tipo_propiedad_val = $_POST['tipo_propiedad'] ?? null;
        $operacion = $_POST['operacion'] ?? null;
        $superficie_terreno = $_POST['superficie_terreno'] ?? null;
        $superficie_construida = $_POST['superficie_construida'] ?? null;
        $dormitorios = $_POST['dormitorios'] ?? null;
        $banos_val = $_POST['banos'] ?? null;
        $estacionamientos_val = $_POST['estacionamientos'] ?? null;
        $bodegas_val = $_POST['bodegas_inmueble'] ?? null;
        $direccion_propiedad = trim($_POST['direccion_propiedad'] ?? '');
        $comuna_propiedad = trim($_POST['comuna_propiedad'] ?? '');
        $disponible_desde = $_POST['disponible_desde'] ?? null;
        $ano_construccion = $_POST['ano_construccion'] ?? null;
        $amoblado = isset($_POST['amoblado']) ? 1 : null;
        $acepta_mascotas = isset($_POST['acepta_mascotas']) ? 1 : null;
        $tiene_lenera = isset($_POST['tiene_lenera']) ? 1 : null;
        $tiene_areas_verdes = isset($_POST['tiene_areas_verdes']) ? 1 : null;
        $tiene_calefaccion = isset($_POST['tiene_calefaccion']) ? 1 : null;
        $tipo_calefaccion_val = $_POST['tipo_calefaccion'] ?? null;
        $es_rural = isset($_POST['es_rural']) ? (int)$_POST['es_rural'] : null;
        $agua_potable = isset($_POST['agua_potable']) ? 1 : null;
        $alcantarillado_val = isset($_POST['alcantarillado']) ? 1 : null;
        $luz_electrica = isset($_POST['luz_electrica']) ? 1 : null;
        $gastos_comunes = $_POST['gastos_comunes'] ?? null;

        // Validar enums
        if ($modalidad && !in_array($modalidad, ['presencial','domicilio','online','mixto'])) $modalidad = null;
        if ($horario_atencion && mb_strlen($horario_atencion) > 100) $horario_atencion = mb_substr($horario_atencion, 0, 100);
        $tpValid = ['casa','departamento','local_comercial','oficina','bodega','terreno','estacionamiento','habitacion','parcela','galpon','sitio'];
        if ($tipo_propiedad_val && !in_array($tipo_propiedad_val, $tpValid)) $tipo_propiedad_val = null;
        if ($operacion && !in_array($operacion, ['arriendo','venta','permuta','arriendo_con_opcion_compra','cesion_derechos'])) $operacion = null;
        if ($superficie_terreno !== null && $superficie_terreno !== '') $superficie_terreno = (float)$superficie_terreno; else $superficie_terreno = null;
        if ($superficie_construida !== null && $superficie_construida !== '') $superficie_construida = (float)$superficie_construida; else $superficie_construida = null;
        if ($dormitorios !== null && $dormitorios !== '') $dormitorios = (int)$dormitorios; else $dormitorios = null;
        if ($banos_val !== null && $banos_val !== '') $banos_val = (int)$banos_val; else $banos_val = null;
        if ($estacionamientos_val !== null && $estacionamientos_val !== '') $estacionamientos_val = (int)$estacionamientos_val; else $estacionamientos_val = null;
        if ($bodegas_val !== null && $bodegas_val !== '') $bodegas_val = (int)$bodegas_val; else $bodegas_val = null;
        if ($disponible_desde && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $disponible_desde)) $disponible_desde = null;
        if ($ano_construccion !== null && $ano_construccion !== '') { $ano_construccion = (int)$ano_construccion; if ($ano_construccion < 1900 || $ano_construccion > 2026) $ano_construccion = null; } else $ano_construccion = null;
        if ($gastos_comunes !== null && $gastos_comunes !== '') $gastos_comunes = (int)$gastos_comunes; else $gastos_comunes = null;
        if (!$tiene_calefaccion) $tipo_calefaccion_val = null;

        // Nullificar campos de otros tipos
        if ($tipo !== 'servicio') { $modalidad = null; $horario_atencion = ''; }
        if ($tipo !== 'inmueble') {
            $tipo_propiedad_val = null; $operacion = null; $superficie_terreno = null; $superficie_construida = null;
            $dormitorios = null; $banos_val = null; $estacionamientos_val = null; $bodegas_val = null;
            $direccion_propiedad = ''; $comuna_propiedad = ''; $disponible_desde = null; $ano_construccion = null;
            $amoblado = null; $acepta_mascotas = null; $tiene_lenera = null; $tiene_areas_verdes = null;
            $tiene_calefaccion = null; $tipo_calefaccion_val = null; $es_rural = null; $agua_potable = null;
            $alcantarillado_val = null; $luz_electrica = null; $gastos_comunes = null;
        }
        // operacion is set by the form for inmueble type

        // Imagen
        $imagenNombre = null;
        if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
                $errores[] = 'La imagen no puede superar los 2 MB.';
            } else {
                $imagenNombre = FileManager::subirImagen($_FILES['imagen'], 'productos/' . $comercioId, 800);
                if (!$imagenNombre) {
                    $errores[] = 'Error al subir la imagen. Solo se permiten JPG, PNG o WebP.';
                }
            }
        }

        if (!empty($errores)) {
            $_SESSION['flash_errors'] = $errores;
            $_SESSION['flash_old'] = ['nombre' => $nombre, 'descripcion' => $descripcion, 'descripcion_detallada' => $descripcion_detallada, 'precio' => $precio, 'activo' => $activo, 'tipo' => $tipo, 'estado' => $estado, 'stock' => $stock, 'condicion' => $condicion, 'modalidad' => $modalidad];
            header('Location: ' . url('/mi-comercio/productos/crear'));
            exit;
        }

        $data = [
            'comercio_id' => $comercioId,
            'tipo'        => $tipo,
            'nombre'      => $nombre,
            'descripcion' => $descripcion ?: null,
            'descripcion_detallada' => $descripcion_detallada ?: null,
            'precio'      => $precio,
            'stock'       => $stock,
            'condicion'   => $condicion,
            'activo'      => $activo,
            'estado'      => $estado,
            'orden'       => Producto::countByComercioId($comercioId),
            'modalidad'             => $modalidad,
            'horario_atencion'      => $horario_atencion ?: null,
            'tipo_propiedad'        => $tipo_propiedad_val,
            'operacion'             => $operacion,
            'superficie_terreno'    => $superficie_terreno,
            'superficie_construida' => $superficie_construida,
            'dormitorios'           => $dormitorios,
            'banos'                 => $banos_val,
            'estacionamientos'      => $estacionamientos_val,
            'bodegas'               => $bodegas_val,
            'direccion_propiedad'   => $direccion_propiedad ?: null,
            'comuna_propiedad'      => $comuna_propiedad ?: null,
            'disponible_desde'      => $disponible_desde,
            'ano_construccion'      => $ano_construccion,
            'amoblado'              => $amoblado,
            'acepta_mascotas'       => $acepta_mascotas,
            'tiene_lenera'          => $tiene_lenera,
            'tiene_areas_verdes'    => $tiene_areas_verdes,
            'tiene_calefaccion'     => $tiene_calefaccion,
            'tipo_calefaccion'      => $tipo_calefaccion_val,
            'es_rural'              => $es_rural,
            'agua_potable'          => $agua_potable,
            'alcantarillado'        => $alcantarillado_val,
            'luz_electrica'         => $luz_electrica,
            'gastos_comunes'        => $gastos_comunes,
        ];
        if ($imagenNombre) {
            $data['imagen'] = $imagenNombre;
        }

        // Imagen 2
        $imagen2Nombre = null;
        if (!empty($_FILES['imagen2']['name']) && $_FILES['imagen2']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['imagen2']['size'] <= 2 * 1024 * 1024) {
                $imagen2Nombre = FileManager::subirImagen($_FILES['imagen2'], 'productos/' . $comercioId, 800);
                if ($imagen2Nombre) $data['imagen2'] = $imagen2Nombre;
            }
        }

        Producto::create($data);

        $_SESSION['flash_success'] = 'Producto creado correctamente.';
        header('Location: ' . url('/mi-comercio/productos'));
        exit;
    }

    /**
     * Formulario de edicion de producto
     */
    public function productoEditar(int $id): void
    {
        if (!$this->isLogueado()) {
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        $uid = $_SESSION['comerciante']['id'];
        $comercio = Comercio::findByRegistradoPorWithCategorias($uid);
        $comercioId = $comercio['id'] ?? 0;
        $producto = Producto::findById($id);

        if (!$comercio || !$producto || $producto['comercio_id'] != $comercioId) {
            $_SESSION['flash_error'] = 'Producto no encontrado.';
            header('Location: ' . url('/mi-comercio/productos'));
            exit;
        }

        $plan = PlanConfig::findBySlug($comercio['plan'] ?? 'freemium');

        $this->render('comerciante/productos/form', [
            'title'          => 'Editar producto — ' . SITE_NAME,
            'noindex'        => true,
            'comercio'       => $comercio,
            'producto'       => $producto,
            'totalProductos' => Producto::countByComercioId($comercioId),
            'maxProductos'   => $plan['max_productos'] ?? 5,
            'plan'           => $plan,
        ]);
    }

    /**
     * Actualizar producto (POST)
     */
    public function productoActualizar(int $id): void
    {
        if (!$this->isLogueado() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        $uid = $_SESSION['comerciante']['id'];
        $comercio = Comercio::findByRegistradoPorWithCategorias($uid);
        $comercioId = $comercio['id'] ?? 0;
        $producto = Producto::findById($id);

        if (!$comercio || !$producto || $producto['comercio_id'] != $comercioId) {
            $_SESSION['flash_error'] = 'Producto no encontrado.';
            header('Location: ' . url('/mi-comercio/productos'));
            exit;
        }

        $nombre      = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio      = $_POST['precio'] ?? null;
        $activo      = isset($_POST['activo']) ? 1 : 0;

        $errores = [];
        if (empty($nombre)) {
            $errores[] = 'El nombre del producto es obligatorio.';
        } elseif (mb_strlen($nombre) > 150) {
            $errores[] = 'El nombre no puede superar los 150 caracteres.';
        }
        if (mb_strlen($descripcion) > 500) {
            $errores[] = 'La descripcion no puede superar los 500 caracteres.';
        }
        if ($precio !== null && $precio !== '') {
            $precio = (int) $precio;
            if ($precio < 0) {
                $errores[] = 'El precio debe ser mayor o igual a 0.';
            }
        } else {
            $precio = null;
        }


        // Campos especificos por tipo
        $modalidad = $_POST['modalidad'] ?? null;
        $horario_atencion = trim($_POST['horario_atencion'] ?? '');
        $tipo_propiedad_val = $_POST['tipo_propiedad'] ?? null;
        $operacion = $_POST['operacion'] ?? null;
        $superficie_terreno = $_POST['superficie_terreno'] ?? null;
        $superficie_construida = $_POST['superficie_construida'] ?? null;
        $dormitorios = $_POST['dormitorios'] ?? null;
        $banos_val = $_POST['banos'] ?? null;
        $estacionamientos_val = $_POST['estacionamientos'] ?? null;
        $bodegas_val = $_POST['bodegas_inmueble'] ?? null;
        $direccion_propiedad = trim($_POST['direccion_propiedad'] ?? '');
        $comuna_propiedad = trim($_POST['comuna_propiedad'] ?? '');
        $disponible_desde = $_POST['disponible_desde'] ?? null;
        $ano_construccion = $_POST['ano_construccion'] ?? null;
        $amoblado = isset($_POST['amoblado']) ? 1 : null;
        $acepta_mascotas = isset($_POST['acepta_mascotas']) ? 1 : null;
        $tiene_lenera = isset($_POST['tiene_lenera']) ? 1 : null;
        $tiene_areas_verdes = isset($_POST['tiene_areas_verdes']) ? 1 : null;
        $tiene_calefaccion = isset($_POST['tiene_calefaccion']) ? 1 : null;
        $tipo_calefaccion_val = $_POST['tipo_calefaccion'] ?? null;
        $es_rural = isset($_POST['es_rural']) ? (int)$_POST['es_rural'] : null;
        $agua_potable = isset($_POST['agua_potable']) ? 1 : null;
        $alcantarillado_val = isset($_POST['alcantarillado']) ? 1 : null;
        $luz_electrica = isset($_POST['luz_electrica']) ? 1 : null;
        $gastos_comunes = $_POST['gastos_comunes'] ?? null;

        // Validar enums
        if ($modalidad && !in_array($modalidad, ['presencial','domicilio','online','mixto'])) $modalidad = null;
        if ($horario_atencion && mb_strlen($horario_atencion) > 100) $horario_atencion = mb_substr($horario_atencion, 0, 100);
        $tpValid = ['casa','departamento','local_comercial','oficina','bodega','terreno','estacionamiento','habitacion','parcela','galpon','sitio'];
        if ($tipo_propiedad_val && !in_array($tipo_propiedad_val, $tpValid)) $tipo_propiedad_val = null;
        if ($operacion && !in_array($operacion, ['arriendo','venta','permuta','arriendo_con_opcion_compra','cesion_derechos'])) $operacion = null;
        if ($superficie_terreno !== null && $superficie_terreno !== '') $superficie_terreno = (float)$superficie_terreno; else $superficie_terreno = null;
        if ($superficie_construida !== null && $superficie_construida !== '') $superficie_construida = (float)$superficie_construida; else $superficie_construida = null;
        if ($dormitorios !== null && $dormitorios !== '') $dormitorios = (int)$dormitorios; else $dormitorios = null;
        if ($banos_val !== null && $banos_val !== '') $banos_val = (int)$banos_val; else $banos_val = null;
        if ($estacionamientos_val !== null && $estacionamientos_val !== '') $estacionamientos_val = (int)$estacionamientos_val; else $estacionamientos_val = null;
        if ($bodegas_val !== null && $bodegas_val !== '') $bodegas_val = (int)$bodegas_val; else $bodegas_val = null;
        if ($disponible_desde && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $disponible_desde)) $disponible_desde = null;
        if ($ano_construccion !== null && $ano_construccion !== '') { $ano_construccion = (int)$ano_construccion; if ($ano_construccion < 1900 || $ano_construccion > 2026) $ano_construccion = null; } else $ano_construccion = null;
        if ($gastos_comunes !== null && $gastos_comunes !== '') $gastos_comunes = (int)$gastos_comunes; else $gastos_comunes = null;
        if (!$tiene_calefaccion) $tipo_calefaccion_val = null;

        // Nullificar campos de otros tipos
        if ($tipo !== 'servicio') { $modalidad = null; $horario_atencion = ''; }
        if ($tipo !== 'inmueble') {
            $tipo_propiedad_val = null; $operacion = null; $superficie_terreno = null; $superficie_construida = null;
            $dormitorios = null; $banos_val = null; $estacionamientos_val = null; $bodegas_val = null;
            $direccion_propiedad = ''; $comuna_propiedad = ''; $disponible_desde = null; $ano_construccion = null;
            $amoblado = null; $acepta_mascotas = null; $tiene_lenera = null; $tiene_areas_verdes = null;
            $tiene_calefaccion = null; $tipo_calefaccion_val = null; $es_rural = null; $agua_potable = null;
            $alcantarillado_val = null; $luz_electrica = null; $gastos_comunes = null;
        }
        // operacion is set by the form for inmueble type

        // Imagen
        $imagenNombre = null;
        if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['imagen']['size'] > 2 * 1024 * 1024) {
                $errores[] = 'La imagen no puede superar los 2 MB.';
            } else {
                $imagenNombre = FileManager::subirImagen($_FILES['imagen'], 'productos/' . $comercioId, 800);
                if (!$imagenNombre) {
                    $errores[] = 'Error al subir la imagen. Solo se permiten JPG, PNG o WebP.';
                }
            }
        }

        if (!empty($errores)) {
            $_SESSION['flash_errors'] = $errores;
            $_SESSION['flash_old'] = ['nombre' => $nombre, 'descripcion' => $descripcion, 'descripcion_detallada' => $descripcion_detallada, 'precio' => $precio, 'activo' => $activo, 'tipo' => $tipo, 'estado' => $estado, 'stock' => $stock, 'condicion' => $condicion, 'modalidad' => $modalidad];
            header('Location: ' . url('/mi-comercio/productos/editar/' . $id));
            exit;
        }

        $data = [
            'tipo'        => $tipo,
            'nombre'      => $nombre,
            'descripcion' => $descripcion ?: null,
            'descripcion_detallada' => $descripcion_detallada ?: null,
            'precio'      => $precio,
            'stock'       => $stock,
            'condicion'   => $condicion,
            'activo'      => $activo,
            'estado'      => $estado,
            'modalidad'             => $modalidad,
            'horario_atencion'      => $horario_atencion ?: null,
            'tipo_propiedad'        => $tipo_propiedad_val,
            'operacion'             => $operacion,
            'superficie_terreno'    => $superficie_terreno,
            'superficie_construida' => $superficie_construida,
            'dormitorios'           => $dormitorios,
            'banos'                 => $banos_val,
            'estacionamientos'      => $estacionamientos_val,
            'bodegas'               => $bodegas_val,
            'direccion_propiedad'   => $direccion_propiedad ?: null,
            'comuna_propiedad'      => $comuna_propiedad ?: null,
            'disponible_desde'      => $disponible_desde,
            'ano_construccion'      => $ano_construccion,
            'amoblado'              => $amoblado,
            'acepta_mascotas'       => $acepta_mascotas,
            'tiene_lenera'          => $tiene_lenera,
            'tiene_areas_verdes'    => $tiene_areas_verdes,
            'tiene_calefaccion'     => $tiene_calefaccion,
            'tipo_calefaccion'      => $tipo_calefaccion_val,
            'es_rural'              => $es_rural,
            'agua_potable'          => $agua_potable,
            'alcantarillado'        => $alcantarillado_val,
            'luz_electrica'         => $luz_electrica,
            'gastos_comunes'        => $gastos_comunes,
        ];
        if ($imagenNombre) {
            // Eliminar imagen anterior
            if (!empty($producto['imagen'])) {
                $this->eliminarImagenProducto($comercioId, $producto['imagen']);
            }
            $data['imagen'] = $imagenNombre;
        }

        // Imagen 2
        if (!empty($_FILES['imagen2']['name']) && $_FILES['imagen2']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['imagen2']['size'] <= 2 * 1024 * 1024) {
                $img2 = FileManager::subirImagen($_FILES['imagen2'], 'productos/' . $comercioId, 800);
                if ($img2) {
                    if (!empty($producto['imagen2'])) {
                        $this->eliminarImagenProducto($comercioId, $producto['imagen2']);
                    }
                    $data['imagen2'] = $img2;
                }
            }
        }

        Producto::update($id, $data);

        $_SESSION['flash_success'] = 'Producto actualizado correctamente.';
        header('Location: ' . url('/mi-comercio/productos'));
        exit;
    }

    /**
     * Eliminar producto (POST)
     */
    public function productoEliminar(int $id): void
    {
        if (!$this->isLogueado() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        $uid = $_SESSION['comerciante']['id'];
        $comercio = Comercio::findByRegistradoPorWithCategorias($uid);
        $comercioId = $comercio['id'] ?? 0;
        $producto = Producto::findById($id);

        if (!$producto || $producto['comercio_id'] != $comercioId) {
            $_SESSION['flash_error'] = 'Producto no encontrado.';
            header('Location: ' . url('/mi-comercio/productos'));
            exit;
        }

        // Eliminar imagenes del disco
        if (!empty($producto['imagen'])) {
            $this->eliminarImagenProducto($comercioId, $producto['imagen']);
        }
        if (!empty($producto['imagen2'])) {
            $this->eliminarImagenProducto($comercioId, $producto['imagen2']);
        }

        Producto::delete($id);

        $_SESSION['flash_success'] = 'Producto eliminado.';
        header('Location: ' . url('/mi-comercio/productos'));
        exit;
    }

    /**
     * Eliminar archivos de imagen de un producto (original, thumb, webp)
     */
    private function eliminarImagenProducto(int $comercioId, string $imagen): void
    {
        $basePath = UPLOAD_PATH . '/productos/' . $comercioId;
        $files = [
            $basePath . '/' . $imagen,
            $basePath . '/thumbs/' . $imagen,
        ];
        // Versiones WebP
        $webpName = pathinfo($imagen, PATHINFO_FILENAME) . '.webp';
        if (pathinfo($imagen, PATHINFO_EXTENSION) !== 'webp') {
            $files[] = $basePath . '/' . $webpName;
            $files[] = $basePath . '/thumbs/' . $webpName;
        }
        foreach ($files as $f) {
            if (file_exists($f)) {
                @unlink($f);
            }
        }
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
            \App\Services\Notification::cambiosPendientesAdmin($comercioId, $nombreComercio);
        } catch (\Throwable $e) {}
    }
}
