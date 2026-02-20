<?php
namespace App\Controllers\Public;

use App\Core\Controller;
use App\Services\Notification;
use App\Services\VisitTracker;

/**
 * Ejercicio de Derechos sobre Datos Personales (ARCO + Portabilidad)
 * Ley 19.628 modificada por Ley 21.719
 */
class DerechosController extends Controller
{
    private array $tiposConfig = [
        'acceso' => [
            'icono'  => "\xF0\x9F\x94\x8D", // magnifying glass
            'titulo' => 'Consultar mis datos',
            'desc'   => 'Quiero saber qué datos personales tienen almacenados sobre mí',
            'placeholder' => 'Describe qué información deseas conocer. Por ejemplo: "Quiero saber qué datos tienen asociados a mi email" o "Necesito saber qué información de mi comercio está almacenada".',
        ],
        'rectificacion' => [
            'icono'  => "\xE2\x9C\x8F\xEF\xB8\x8F", // pencil
            'titulo' => 'Corregir mis datos',
            'desc'   => 'Mis datos están incorrectos o incompletos y quiero corregirlos',
            'placeholder' => 'Indica qué datos son incorrectos y cuál es la información correcta. Por ejemplo: "Mi dirección aparece como Av. Chile 100, pero la correcta es Av. Chile 200".',
        ],
        'cancelacion' => [
            'icono'  => "\xF0\x9F\x97\x91\xEF\xB8\x8F", // wastebasket
            'titulo' => 'Eliminar mis datos / Darme de baja',
            'desc'   => 'Quiero que eliminen mis datos personales y/o mi comercio de la plataforma',
            'placeholder' => 'Indica qué deseas eliminar. Por ejemplo: "Quiero eliminar mi comercio y todos los datos asociados" o "Quiero que borren mi reseña publicada en el comercio X".',
        ],
        'oposicion' => [
            'icono'  => "\xF0\x9F\x9A\xAB", // prohibited
            'titulo' => 'Oponerme al uso de mis datos',
            'desc'   => 'No quiero que traten mis datos para cierta finalidad',
            'placeholder' => 'Indica a qué tratamiento te opones. Por ejemplo: "No quiero que mis datos aparezcan en el mapa" o "No quiero que mi email sea utilizado para comunicaciones".',
        ],
        'portabilidad' => [
            'icono'  => "\xF0\x9F\x93\xA6", // package
            'titulo' => 'Recibir copia de mis datos',
            'desc'   => 'Quiero recibir mis datos en formato digital descargable',
            'placeholder' => 'Indica qué datos necesitas recibir. Te los enviaremos en formato CSV o JSON al email proporcionado.',
        ],
    ];

    private array $motivosBaja = [
        'cierre_negocio'     => 'Mi comercio cerró o ya no opera',
        'no_autorizo'        => 'No autoricé la publicación de mis datos',
        'cambio_plataforma'  => 'Prefiero usar otra plataforma',
        'privacidad'         => 'Razones de privacidad personal',
        'otro'               => 'Otro motivo',
    ];

    /**
     * GET /derechos
     */
    public function index(): void
    {
        VisitTracker::track(null, '/derechos', 'pagina');

        $tipoSeleccionado = $_GET['tipo'] ?? '';

        $this->render('public/derechos', [
            'title'             => 'Ejercicio de Derechos — ' . SITE_NAME,
            'description'       => 'Ejerce tus derechos ARCO sobre datos personales en ' . SITE_NAME,
            'breadcrumbs'       => [
                ['label' => 'Inicio', 'url' => '/'],
                ['label' => 'Ejercicio de Derechos'],
            ],
            'extraCss'          => 'css/derechos.css',
            'tiposConfig'       => $this->tiposConfig,
            'motivosBaja'       => $this->motivosBaja,
            'tipoSeleccionado'  => $tipoSeleccionado,
            'mensajeExito'      => '',
            'mensajeError'      => '',
            'errores'           => [],
            'old'               => [],
        ]);
    }

    /**
     * POST /derechos
     */
    public function store(): void
    {
        $tipo             = trim($_POST['tipo'] ?? '');
        $nombre           = trim($_POST['nombre'] ?? '');
        $email            = trim($_POST['email'] ?? '');
        $rut              = trim($_POST['rut'] ?? '');
        $telefono         = trim($_POST['telefono'] ?? '');
        $nombreComercio   = trim($_POST['nombre_comercio'] ?? '');
        $motivoBaja       = trim($_POST['motivo_baja'] ?? '');
        $descripcion      = trim($_POST['descripcion'] ?? '');
        $esComerciante    = isset($_POST['es_comerciante']) ? 1 : 0;

        $old = $_POST;
        $errores = [];

        // Validaciones
        if (!array_key_exists($tipo, $this->tiposConfig)) {
            $errores[] = 'Selecciona un tipo de solicitud válido.';
        }
        if (empty($nombre) || mb_strlen($nombre) < 3) {
            $errores[] = 'El nombre es obligatorio (mínimo 3 caracteres).';
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Ingresa un correo electrónico válido.';
        }
        if (empty($descripcion) || mb_strlen($descripcion) < 10) {
            $errores[] = 'La descripción debe tener al menos 10 caracteres.';
        }
        if (mb_strlen($descripcion) > 5000) {
            $errores[] = 'La descripción no puede superar los 5.000 caracteres.';
        }

        // Rate limiting: máx 3 solicitudes por email en 24h
        if (empty($errores)) {
            $count = $this->db->fetch(
                "SELECT COUNT(*) as total FROM solicitudes_arco
                 WHERE email = ? AND fecha_solicitud > DATE_SUB(NOW(), INTERVAL 24 HOUR)",
                [$email]
            );
            if (($count['total'] ?? 0) >= 3) {
                $errores[] = 'Has enviado demasiadas solicitudes en las últimas 24 horas. Intenta mañana.';
            }
        }

        if (!empty($errores)) {
            $this->render('public/derechos', [
                'title'             => 'Ejercicio de Derechos — ' . SITE_NAME,
                'description'       => 'Ejerce tus derechos ARCO sobre datos personales en ' . SITE_NAME,
                'breadcrumbs'       => [
                    ['label' => 'Inicio', 'url' => '/'],
                    ['label' => 'Ejercicio de Derechos'],
                ],
                'extraCss'          => 'css/derechos.css',
                'tiposConfig'       => $this->tiposConfig,
                'motivosBaja'       => $this->motivosBaja,
                'tipoSeleccionado'  => $tipo,
                'mensajeExito'      => '',
                'mensajeError'      => '',
                'errores'           => $errores,
                'old'               => $old,
            ]);
            return;
        }

        // Construir descripcion con metadatos
        $descCompleta = $descripcion;
        if ($esComerciante && !empty($nombreComercio)) {
            $descCompleta = "[COMERCIANTE: {$nombreComercio}] " . $descCompleta;
        }
        if ($tipo === 'cancelacion' && !empty($motivoBaja)) {
            $motivoTexto = $this->motivosBaja[$motivoBaja] ?? $motivoBaja;
            $descCompleta = "[MOTIVO: {$motivoTexto}] " . $descCompleta;
        }
        if (!empty($telefono)) {
            $descCompleta .= "\n[TELÉFONO CONTACTO: {$telefono}]";
        }

        // Insertar en BD
        $idSolicitud = $this->db->insert('solicitudes_arco', [
            'tipo'        => $tipo,
            'nombre'      => $nombre,
            'email'       => $email,
            'rut'         => !empty($rut) ? $rut : null,
            'descripcion' => $descCompleta,
            'ip'          => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        ]);

        $tipoTexto = $this->tiposConfig[$tipo]['titulo'] ?? $tipo;

        // Enviar emails
        Notification::solicitudArcoAdmin([
            'id'               => $idSolicitud,
            'tipo'             => $tipo,
            'tipo_texto'       => $tipoTexto,
            'nombre'           => $nombre,
            'email'            => $email,
            'rut'              => $rut,
            'telefono'         => $telefono,
            'es_comerciante'   => $esComerciante,
            'nombre_comercio'  => $nombreComercio,
            'motivo_baja'      => !empty($motivoBaja) ? ($this->motivosBaja[$motivoBaja] ?? $motivoBaja) : '',
            'descripcion'      => $descripcion,
            'fecha_limite'     => date('d/m/Y', strtotime('+14 days')),
        ]);

        Notification::solicitudArcoConfirmacion($email, [
            'id'         => $idSolicitud,
            'tipo_texto' => $tipoTexto,
            'nombre'     => $nombre,
            'fecha'      => date('d/m/Y H:i'),
        ]);

        $mensajeExito = "Solicitud #{$idSolicitud} recibida correctamente. "
            . "Recibirás confirmación en tu email. Plazo de respuesta: 10 días hábiles.";

        $this->render('public/derechos', [
            'title'             => 'Ejercicio de Derechos — ' . SITE_NAME,
            'description'       => 'Ejerce tus derechos ARCO sobre datos personales en ' . SITE_NAME,
            'breadcrumbs'       => [
                ['label' => 'Inicio', 'url' => '/'],
                ['label' => 'Ejercicio de Derechos'],
            ],
            'extraCss'          => 'css/derechos.css',
            'tiposConfig'       => $this->tiposConfig,
            'motivosBaja'       => $this->motivosBaja,
            'tipoSeleccionado'  => '',
            'mensajeExito'      => $mensajeExito,
            'mensajeError'      => '',
            'errores'           => [],
            'old'               => [],
        ]);
    }
}
