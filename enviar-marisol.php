<?php
/**
 * Script temporal para enviar correo personalizado a Marisol
 *
 * USO: Ejecutar UNA sola vez desde el navegador y luego ELIMINAR
 * URL:  https://v2.regalos.purranque.info/enviar-marisol.php?token=regalos2026smtp
 */

// ── Proteccion por token ──
$TOKEN = 'regalos2026smtp';
if (($_GET['token'] ?? '') !== $TOKEN) {
    http_response_code(403);
    die('Acceso denegado. Usa ?token=...');
}

// ── Bootstrap minimo ──
define('BASE_PATH', __DIR__);
require_once BASE_PATH . '/config/app.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/app/Core/Database.php';
require_once BASE_PATH . '/app/Models/Configuracion.php';
require_once BASE_PATH . '/app/Services/Mailer.php';

echo "<pre style='font-family:monospace;font-size:14px;'>\n";
echo "=== Envio de correo personalizado a Marisol ===\n\n";

$mailer = new \App\Services\Mailer();

$result = $mailer->send(
    'inostrozaveramarisol@gmail.com',
    '¡Por supuesto! Los artesanos son bienvenidos — Regalos Purranque',
    'contacto-instrucciones-registro',
    [
        'saludoPersonalizado' => 'Estimada Marisol,',

        'parrafoApertura' => 'Recibimos su consulta a trav&eacute;s de nuestro formulario de contacto y nos complace enormemente su inter&eacute;s. La respuesta es s&iacute;, absolutamente. Los artesanos son m&aacute;s que bienvenidos en Regalos Purranque. De hecho, su consulta nos parece tan valiosa que hemos decidido crear una nueva categor&iacute;a especial de <strong>Artesan&iacute;as</strong> &#129330; dentro de la plataforma, dedicada exclusivamente a visibilizar el trabajo de los artesanos de Purranque y la provincia. Los productos hechos a mano, con identidad local, son justamente el tipo de regalo que la gente busca cuando quiere obsequiar algo &uacute;nico y con significado.',

        'categoriaEspecial' => 'Artesan&iacute;as &#129330;',
        'categoriaDescripcion' => 'Nueva, creada especialmente para emprendimientos como el suyo. Los productos artesanales hechos a mano tienen un lugar destacado en nuestra plataforma.',

        'descripcionNegocio' => 'cu&eacute;ntenos qu&eacute; tipo de artesan&iacute;as elabora, materiales que utiliza, t&eacute;cnicas, si hace productos personalizados, etc.',

        'parrafoFechaEspecial' => 'El <strong>D&iacute;a Internacional de la Mujer</strong> est&aacute; a solo dos semanas. Si registra su emprendimiento ahora, estar&aacute; visible cuando la gente busque regalos para esta fecha. Los productos artesanales hechos a mano son uno de los regalos m&aacute;s valorados en esta celebraci&oacute;n.',

        'datos' => [
            'nombre' => 'Marisol Inostroza Vera',
            'email'  => 'inostrozaveramarisol@gmail.com',
            'asunto' => 'Consulta artesana',
        ],
        'registroUrl' => SITE_URL . '/registrar-comercio',
    ]
);

if ($result) {
    echo "OK -> Correo enviado exitosamente a inostrozaveramarisol@gmail.com\n";
} else {
    echo "FAIL -> No se pudo enviar el correo\n";
}

echo "\n=== ELIMINAR este archivo del servidor despues de usar ===\n";
echo "rm " . __FILE__ . "\n";
echo "</pre>";
