<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Core\Database;

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

        if (empty($email) || empty($password)) {
            $_SESSION['flash_error'] = 'Ingresa tu email y contraseña.';
            $_SESSION['flash_old'] = ['email' => $email];
            header('Location: ' . url('/mi-comercio/login'));
            exit;
        }

        // Buscar usuario comerciante
        $user = $this->db->fetch(
            "SELECT * FROM admin_usuarios WHERE email = ? AND rol = 'comerciante'",
            [$email]
        );

        if (!$user || !password_verify($password, $user['password_hash'])) {
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

        // Crear sesión del comerciante (separada del admin)
        $_SESSION['comerciante'] = [
            'id'     => (int)$user['id'],
            'nombre' => $user['nombre'],
            'email'  => $user['email'],
        ];

        // Actualizar último login
        $this->db->update('admin_usuarios', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$user['id']]);

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
        $comercio = $this->db->fetch(
            "SELECT c.*, 
                    GROUP_CONCAT(DISTINCT cat.nombre SEPARATOR ', ') as categorias_nombres
             FROM comercios c
             LEFT JOIN comercio_categoria cc ON c.id = cc.comercio_id
             LEFT JOIN categorias cat ON cc.categoria_id = cat.id
             WHERE c.registrado_por = ?
             GROUP BY c.id
             LIMIT 1",
            [$uid]
        );

        // Verificar si tiene cambios pendientes
        $pendientes = null;
        if ($comercio) {
            $pendientes = $this->db->fetch(
                "SELECT id, created_at FROM comercio_cambios_pendientes 
                 WHERE comercio_id = ? AND estado = 'pendiente' 
                 ORDER BY created_at DESC LIMIT 1",
                [$comercio['id']]
            );
        }

        // Datos del plan
        $plan = null;
        if ($comercio) {
            $plan = $this->db->fetch(
                "SELECT * FROM planes_config WHERE slug = ?",
                [$comercio['plan']]
            );
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
        $comercio = $this->db->fetch("SELECT * FROM comercios WHERE registrado_por = ? LIMIT 1", [$uid]);

        if (!$comercio) {
            $_SESSION['flash_error'] = 'No se encontró tu comercio.';
            header('Location: ' . url('/mi-comercio'));
            exit;
        }

        // Categorías y fechas
        $categorias = $this->db->fetchAll("SELECT id, nombre, icono FROM categorias WHERE activo = 1 ORDER BY orden");
        $fechas = $this->db->fetchAll("SELECT id, nombre, icono, tipo FROM fechas_especiales WHERE activo = 1 ORDER BY tipo, nombre");

        // Categorías actuales del comercio
        $catActuales = $this->db->fetchAll(
            "SELECT categoria_id, es_principal FROM comercio_categoria WHERE comercio_id = ?",
            [$comercio['id']]
        );
        $catIds = array_column($catActuales, 'categoria_id');
        $catPrincipal = 0;
        foreach ($catActuales as $ca) {
            if ($ca['es_principal']) { $catPrincipal = $ca['categoria_id']; break; }
        }

        // Fechas actuales
        $fechaActuales = $this->db->fetchAll(
            "SELECT fecha_id FROM comercio_fecha WHERE comercio_id = ?",
            [$comercio['id']]
        );
        $fechaIds = array_column($fechaActuales, 'fecha_id');

        // Plan actual para límites
        $plan = $this->db->fetch("SELECT * FROM planes_config WHERE slug = ?", [$comercio['plan']]);

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
        $comercio = $this->db->fetch("SELECT * FROM comercios WHERE registrado_por = ? LIMIT 1", [$uid]);

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
            $nuevoLogo = $this->subirImagen($_FILES['logo'], 'logos');
            if ($nuevoLogo) {
                $cambios['logo'] = ['anterior' => $comercio['logo'], 'nuevo' => $nuevoLogo];
            }
        }
        if (!empty($_FILES['portada']['tmp_name']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
            $nuevaPortada = $this->subirImagen($_FILES['portada'], 'portadas');
            if ($nuevaPortada) {
                $cambios['portada'] = ['anterior' => $comercio['portada'], 'nuevo' => $nuevaPortada];
            }
        }

        // Categorías
        $nuevasCats = array_map('intval', $_POST['categorias'] ?? []);
        $actualesCats = array_map('intval', $this->db->fetchAll(
            "SELECT categoria_id FROM comercio_categoria WHERE comercio_id = ?",
            [$comercio['id']]
        ) ? array_column($this->db->fetchAll("SELECT categoria_id FROM comercio_categoria WHERE comercio_id = ?", [$comercio['id']]), 'categoria_id') : []);
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
        $actualesFechas = array_map('intval', $this->db->fetchAll(
            "SELECT fecha_id FROM comercio_fecha WHERE comercio_id = ?",
            [$comercio['id']]
        ) ? array_column($this->db->fetchAll("SELECT fecha_id FROM comercio_fecha WHERE comercio_id = ?", [$comercio['id']]), 'fecha_id') : []);
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
        $this->db->insert('comercio_cambios_pendientes', [
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

    private function subirImagen(array $file, string $carpeta): ?string
    {
        $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $permitidos)) return null;
        if ($file['size'] > 2 * 1024 * 1024) return null;

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'jpg';
        $nombre = uniqid() . '-' . time() . '.' . $ext;
        $destino = BASE_PATH . '/assets/img/' . $carpeta . '/' . $nombre;

        $dir = dirname($destino);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        return move_uploaded_file($file['tmp_name'], $destino) ? $nombre : null;
    }

    private function notificarCambios(int $comercioId, string $nombreComercio): void
    {
        try {
            $admin = $this->db->fetch("SELECT email FROM admin_usuarios WHERE rol IN ('admin','superadmin') AND activo = 1 LIMIT 1");
            if (!$admin) return;

            $asunto = "✏️ Cambios pendientes: {$nombreComercio}";
            $cuerpo  = "El comercio «{$nombreComercio}» ha enviado cambios para revisión.\n\n";
            $cuerpo .= "Revísalos en: " . SITE_URL . "/admin/comercios/editar/{$comercioId}\n";

            $headers  = "From: " . SITE_NAME . " <noreply@regalospurranque.cl>\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            mail($admin['email'], $asunto, $cuerpo, $headers);
        } catch (\Throwable $e) {}
    }
}
