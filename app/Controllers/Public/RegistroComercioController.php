<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Models\AdminUsuario;
use App\Models\Categoria;
use App\Models\Comercio;
use App\Models\FechaEspecial;
use App\Services\Captcha;
use App\Services\FileManager;

/**
 * Registro público de comercios (Plan Freemium)
 * Flujo: Paso 1 (crear cuenta) → Paso 2 (datos comercio) → Confirmación
 * Todo queda con activo = 0 hasta aprobación del admin
 * 
 * NOTA: La validación CSRF la maneja el middleware global (CsrfMiddleware)
 * No se valida aquí para evitar doble verificación y regeneración de token.
 */
class RegistroComercioController extends Controller
{
    public function index(): void
    {
        if ($this->tieneSessionRegistro()) {
            header('Location: ' . url('/registrar-comercio/datos'));
            exit;
        }

        $this->render('public/registro-comercio/cuenta', [
            'title'       => 'Registra tu comercio gratis — ' . SITE_NAME,
            'description' => 'Publica tu negocio en el directorio digital de Purranque. Registro gratuito.',
            'noindex'     => true,
        ]);
    }

    public function storeCuenta(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . url('/registrar-comercio'));
            exit;
        }

        // CSRF ya validado por middleware global

        // Validar Turnstile
        if (!Captcha::verify($_POST['cf-turnstile-response'] ?? null)) {
            $_SESSION['flash_error'] = 'Verificación anti-bot fallida. Intenta nuevamente.';
            $_SESSION['flash_old'] = ['nombre' => $_POST['nombre'] ?? '', 'email' => $_POST['email'] ?? '', 'telefono' => $_POST['telefono'] ?? ''];
            header('Location: ' . url('/registrar-comercio'));
            exit;
        }

        $nombre   = trim($_POST['nombre'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $telefono = trim($_POST['telefono'] ?? '');
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password_confirm'] ?? '';

        $errores = [];
        if (strlen($nombre) < 3) $errores[] = 'El nombre debe tener al menos 3 caracteres.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Ingresa un email válido.';
        if (strlen($password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
        if ($password !== $password2) $errores[] = 'Las contraseñas no coinciden.';

        if (empty($errores)) {
            $existe = AdminUsuario::findByEmail($email);
            if ($existe) {
                $errores[] = 'Ya existe una cuenta con este email.';
            }
        }

        if (!empty($errores)) {
            $_SESSION['flash_errors'] = $errores;
            $_SESSION['flash_old'] = ['nombre' => $nombre, 'email' => $email, 'telefono' => $telefono];
            header('Location: ' . url('/registrar-comercio'));
            exit;
        }

        $userId = AdminUsuario::create([
            'nombre'        => $nombre,
            'email'         => $email,
            'telefono'      => $telefono,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'rol'           => 'comerciante',
            'activo'        => 0,
            'site_id'       => 1,
        ]);

        $_SESSION['registro_uid']    = $userId;
        $_SESSION['registro_nombre'] = $nombre;
        $_SESSION['registro_email']  = $email;

        header('Location: ' . url('/registrar-comercio/datos'));
        exit;
    }

    public function datos(): void
    {
        if (!$this->tieneSessionRegistro()) {
            header('Location: ' . url('/registrar-comercio'));
            exit;
        }

        $uid = $_SESSION['registro_uid'];
        $yaRegistro = Comercio::findByRegistradoPor($uid);
        if ($yaRegistro) {
            $this->render('public/registro-comercio/ya-registrado', [
                'title' => 'Comercio ya registrado — ' . SITE_NAME,
                'noindex' => true,
            ]);
            return;
        }

        $categorias = Categoria::getActiveForSelect();
        $fechas = FechaEspecial::getActiveForSelect();

        $this->render('public/registro-comercio/datos', [
            'title'         => 'Datos de tu comercio — ' . SITE_NAME,
            'noindex'       => true,
            'categorias'    => $categorias,
            'fechas'        => $fechas,
            'nombreUsuario' => $_SESSION['registro_nombre'],
        ]);
    }

    public function storeDatos(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->tieneSessionRegistro()) {
            header('Location: ' . url('/registrar-comercio'));
            exit;
        }

        // CSRF ya validado por middleware global

        // Validar Turnstile
        if (!Captcha::verify($_POST['cf-turnstile-response'] ?? null)) {
            $_SESSION['flash_error'] = 'Verificación anti-bot fallida. Intenta nuevamente.';
            $_SESSION['flash_old'] = $_POST;
            header('Location: ' . url('/registrar-comercio/datos'));
            exit;
        }

        $uid = $_SESSION['registro_uid'];
        $nombre      = trim($_POST['nombre'] ?? '');
        $whatsapp    = trim($_POST['whatsapp'] ?? '');

        $errores = [];
        if (strlen($nombre) < 3) $errores[] = 'El nombre del comercio es obligatorio (mín. 3 caracteres).';
        if (empty($whatsapp)) $errores[] = 'El WhatsApp es obligatorio.';

        if (!empty($errores)) {
            $_SESSION['flash_errors'] = $errores;
            $_SESSION['flash_old'] = $_POST;
            header('Location: ' . url('/registrar-comercio/datos'));
            exit;
        }

        $slug = $this->generarSlug($nombre);

        // Subir imágenes (FileManager valida MIME con finfo, redimensiona y genera WebP)
        $logoPath = $portadaPath = null;
        if (!empty($_FILES['logo']['tmp_name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoPath = FileManager::subirImagen($_FILES['logo'], 'logos', 800) ?: null;
        }
        if (!empty($_FILES['portada']['tmp_name']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
            $portadaPath = FileManager::subirImagen($_FILES['portada'], 'portadas', 1200) ?: null;
        }

        // Red social (solo 1 en plan freemium)
        $redTipo = $_POST['red_social_tipo'] ?? '';
        $redUrl  = trim($_POST['red_social_url'] ?? '');
        $redes = array_fill_keys(['facebook','instagram','tiktok','youtube','x_twitter','linkedin','telegram','pinterest'], null);
        if (!empty($redTipo) && !empty($redUrl) && isset($redes[$redTipo])) {
            $redes[$redTipo] = $redUrl;
        }

        $comercioId = Comercio::create([
            'nombre'         => $nombre,
            'slug'           => $slug,
            'descripcion'    => trim($_POST['descripcion'] ?? ''),
            'telefono'       => trim($_POST['telefono'] ?? ''),
            'whatsapp'       => $whatsapp,
            'email'          => trim($_POST['email_comercio'] ?? ''),
            'sitio_web'      => trim($_POST['sitio_web'] ?? ''),
            'direccion'      => trim($_POST['direccion'] ?? ''),
            'lat'            => !empty($_POST['lat']) ? (float)$_POST['lat'] : null,
            'lng'            => !empty($_POST['lng']) ? (float)$_POST['lng'] : null,
            'logo'           => $logoPath,
            'portada'        => $portadaPath,
            'plan'           => 'freemium',
            'activo'         => 0,
            'destacado'      => 0,
            'registrado_por' => $uid,
            'facebook'       => $redes['facebook'],
            'instagram'      => $redes['instagram'],
            'tiktok'         => $redes['tiktok'],
            'youtube'        => $redes['youtube'],
            'x_twitter'      => $redes['x_twitter'],
            'linkedin'       => $redes['linkedin'],
            'telegram'       => $redes['telegram'],
            'pinterest'      => $redes['pinterest'],
        ]);

        // Categorías
        $catIds = array_filter(array_map('intval', $_POST['categorias'] ?? []), fn($id) => $id > 0);
        $principal = (int)($_POST['categoria_principal'] ?? 0);
        Comercio::syncCategorias($comercioId, $catIds, $principal);

        // Fechas especiales
        $fechaIds = array_filter(array_map('intval', $_POST['fechas'] ?? []), fn($id) => $id > 0);
        Comercio::syncFechas($comercioId, $fechaIds);

        Comercio::recalcularCalidad($comercioId);

        // Notificar admin
        $this->notificarAdmin($comercioId, $nombre);

        unset($_SESSION['registro_uid'], $_SESSION['registro_nombre'], $_SESSION['registro_email']);

        header('Location: ' . url('/registrar-comercio/gracias'));
        exit;
    }

    public function gracias(): void
    {
        $this->render('public/registro-comercio/gracias', [
            'title'   => '¡Registro exitoso! — ' . SITE_NAME,
            'noindex' => true,
        ]);
    }

    // ── Helpers ──────────────────────────────────────────

    private function tieneSessionRegistro(): bool
    {
        return !empty($_SESSION['registro_uid']);
    }

    private function generarSlug(string $nombre): string
    {
        $slug = slugify($nombre);
        $original = $slug;
        $i = 1;
        while ($this->db->fetch("SELECT id FROM comercios WHERE slug = ?", [$slug])) {
            $slug = $original . '-' . $i++;
        }
        return $slug;
    }

    private function notificarAdmin(int $comercioId, string $nombreComercio): void
    {
        try {
            $adminEmail = AdminUsuario::getFirstAdminEmail();
            if (!$adminEmail) return;

            $asunto = "🏪 Nuevo comercio registrado: {$nombreComercio}";
            $cuerpo  = "Se ha registrado un nuevo comercio en " . SITE_NAME . ".\n\n";
            $cuerpo .= "Comercio: {$nombreComercio}\n";
            $cuerpo .= "ID: {$comercioId}\n";
            $cuerpo .= "Estado: Pendiente de aprobación\n\n";
            $cuerpo .= "Revísalo en: " . SITE_URL . "/admin/comercios/editar/{$comercioId}\n";

            $headers  = "From: " . SITE_NAME . " <noreply@regalospurranque.cl>\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            mail($adminEmail, $asunto, $cuerpo, $headers);
        } catch (\Throwable $e) {}
    }
}
