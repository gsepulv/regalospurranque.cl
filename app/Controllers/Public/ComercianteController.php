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
        if (mb_strlen($sitio_web) > 255) {
            $errors[] = 'El sitio web no puede superar los 255 caracteres.';
        }
        if (mb_strlen($direccion) < 5 || mb_strlen($direccion) > 255) {
            $errors[] = 'La dirección debe tener entre 5 y 255 caracteres.';
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
            $comprobante = FileManager::subirImagen($_FILES['comprobante'], 'comprobantes', 1200);
            if (!$comprobante) {
                $_SESSION['flash_error'] = 'No se pudo subir el comprobante. Verifica que sea una imagen válida (JPG, PNG, WebP) de máximo 2 MB.';
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
