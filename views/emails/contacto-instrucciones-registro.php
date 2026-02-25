<?php
/**
 * Template: Instrucciones de registro para contactos
 *
 * Variables disponibles (desde renderTemplate):
 *   $siteName, $siteUrl, $year, $logoUrl
 *   $datos (array con nombre, email, asunto)
 *   $registroUrl
 *
 * Variables opcionales de personalizacion (para correos manuales):
 *   $saludoPersonalizado    — "Estimada Marisol" (default: "Hola" + nombre)
 *   $parrafoApertura        — Parrafo personalizado de apertura
 *   $categoriaEspecial      — Nombre de categoria sugerida (ej: "Artesanias")
 *   $categoriaDescripcion   — Detalle de la categoria (ej: "nueva, creada especialmente...")
 *   $descripcionNegocio     — Que incluir en la descripcion (ej: "que tipo de artesanias...")
 *   $parrafoFechaEspecial   — Parrafo sobre fecha especial proxima
 *
 * SECCIONES MARCADAS:
 *   <!-- EDITABLE: ... -->   = El admin personaliza al usar correo manual
 *   <!-- ESTANDAR: ... -->   = Contenido fijo, normalmente no se modifica
 */

// Defaults
$nombre = $datos['nombre'] ?? '';
$saludo = $saludoPersonalizado ?? ($nombre ? "&iexcl;Hola <strong>{$nombre}</strong>!" : "&iexcl;Hola!");
$apertura = $parrafoApertura ?? "Muchas gracias por escribirnos y por su inter&eacute;s en ser parte de <strong>Regalos Purranque</strong>. La respuesta es s&iacute;, &iexcl;por supuesto que puede incluirse!";
$catEspecial = $categoriaEspecial ?? '';
$catDesc = $categoriaDescripcion ?? '';
$descNegocio = $descripcionNegocio ?? 'cu&eacute;ntenos qu&eacute; vende, qu&eacute; marcas maneja, si vende por cat&aacute;logo y stock, etc.';
$fechaEspecial = $parrafoFechaEspecial ?? '';
$regUrl = $registroUrl ?? ($siteUrl . '/registrar-comercio');
?>

<!-- ═══════════════════════════════════════════════════════════
     EDITABLE: Saludo personalizado
     Ejemplos: "Estimada Marisol,", "Estimado Jorge,", "¡Hola!"
     ═══════════════════════════════════════════════════════════ -->
<p style="color:#2D2D2D;margin:0 0 16px;line-height:1.6;font-size:16px;">
    <?= $saludo ?>
</p>

<!-- ═══════════════════════════════════════════════════════════
     EDITABLE: Parrafo de apertura personalizado
     Cambia segun cada contacto: artesanos, pasteleros, etc.
     ═══════════════════════════════════════════════════════════ -->
<p style="color:#4A4A4A;margin:0 0 20px;line-height:1.7;font-size:15px;">
    <?= $apertura ?>
</p>

<?php if ($catEspecial): ?>
<!-- ═══════════════════════════════════════════════════════════
     EDITABLE: Mencion de categoria especial
     Cambia segun el rubro: Artesanias, Pasteleria, Belleza, etc.
     ═══════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
    <tr>
        <td style="background:#FFF8F0;border:2px solid #D4A017;border-radius:8px;padding:18px 20px;">
            <p style="margin:0 0 6px;font-size:16px;font-weight:bold;color:#CC0000;">&#127991; Categor&iacute;a: <?= $catEspecial ?></p>
            <?php if ($catDesc): ?>
            <p style="margin:0;color:#4A4A4A;font-size:14px;line-height:1.6;"><?= $catDesc ?></p>
            <?php endif; ?>
        </td>
    </tr>
</table>
<?php endif; ?>

<?php if ($fechaEspecial): ?>
<!-- ═══════════════════════════════════════════════════════════
     EDITABLE: Parrafo de fecha especial
     Cambia segun la fecha mas cercana: Dia de la Mujer, Madre, etc.
     ═══════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
    <tr>
        <td style="background:#FFF8F0;border-left:4px solid #CC0000;border-radius:4px;padding:16px 20px;">
            <p style="margin:0;color:#4A4A4A;font-size:15px;line-height:1.7;"><?= $fechaEspecial ?></p>
        </td>
    </tr>
</table>
<?php endif; ?>


<!-- ═══════════════════════════════════════════════════════════
     ESTANDAR: No editar - Que es Regalos Purranque
     ═══════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
    <tr>
        <td style="border-bottom:3px solid #D4A017;padding:0 0 8px;">
            <p style="margin:0;font-size:18px;font-weight:bold;color:#CC0000;">&#127873; &iquest;Qu&eacute; es Regalos Purranque?</p>
        </td>
    </tr>
</table>
<p style="color:#4A4A4A;margin:0 0 20px;line-height:1.7;font-size:15px;">
    Regalos Purranque es una plataforma web que re&uacute;ne a los comercios, emprendedores y artesanos de Purranque y la provincia en un solo lugar. Nuestro objetivo es que las personas encuentren f&aacute;cilmente d&oacute;nde comprar regalos para cada ocasi&oacute;n especial, apoyando al comercio local.
</p>


<!-- ═══════════════════════════════════════════════════════════
     ESTANDAR: No editar - Beneficios del Plan Gratuito
     ═══════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 8px;">
    <tr>
        <td style="border-bottom:3px solid #D4A017;padding:0 0 8px;">
            <p style="margin:0;font-size:18px;font-weight:bold;color:#CC0000;">&#11088; Beneficios de estar en la plataforma</p>
        </td>
    </tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;border:1px solid #E8D5B0;border-radius:8px;overflow:hidden;">
    <tr>
        <td style="background:#FFF8F0;padding:12px 16px;border-bottom:1px solid #E8D5B0;">
            <p style="margin:0;color:#2D2D2D;font-size:14px;">&#10004;&#65039; <strong>P&aacute;gina exclusiva</strong> de su negocio con fotos, contacto y horarios</p>
        </td>
    </tr>
    <tr>
        <td style="background:#FFFFFF;padding:12px 16px;border-bottom:1px solid #E8D5B0;">
            <p style="margin:0;color:#2D2D2D;font-size:14px;">&#10004;&#65039; <strong>Visibilidad</strong> en b&uacute;squedas locales de regalos en Purranque</p>
        </td>
    </tr>
    <tr>
        <td style="background:#FFF8F0;padding:12px 16px;border-bottom:1px solid #E8D5B0;">
            <p style="margin:0;color:#2D2D2D;font-size:14px;">&#10004;&#65039; Aparici&oacute;n en <strong>fechas especiales</strong> (D&iacute;a de la Madre, Navidad, etc.)</p>
        </td>
    </tr>
    <tr>
        <td style="background:#FFFFFF;padding:12px 16px;border-bottom:1px solid #E8D5B0;">
            <p style="margin:0;color:#2D2D2D;font-size:14px;">&#10004;&#65039; Enlace directo a su <strong>WhatsApp</strong> y redes sociales</p>
        </td>
    </tr>
    <tr>
        <td style="background:#FFF8F0;padding:12px 16px;">
            <p style="margin:0;color:#2D2D2D;font-size:14px;">&#10004;&#65039; <strong>Panel de administraci&oacute;n</strong> para gestionar su informaci&oacute;n</p>
        </td>
    </tr>
</table>

<!-- Caja Plan Gratuito -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
    <tr>
        <td style="background:#FFF8F0;border:3px solid #D4A017;border-radius:10px;padding:20px;text-align:center;">
            <p style="margin:0 0 6px;font-size:20px;font-weight:bold;color:#CC0000;">&#127881; Plan Gratuito &mdash; Sin Costo por 30 d&iacute;as</p>
            <p style="margin:0;font-size:15px;color:#4A4A4A;">Estamos en etapa Beta. Su negocio ser&aacute; publicado sin ning&uacute;n costo durante 30 d&iacute;as.</p>
        </td>
    </tr>
</table>


<!-- ═══════════════════════════════════════════════════════════
     ESTANDAR: No editar - Como registrarse (pasos)
     ═══════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 8px;">
    <tr>
        <td style="border-bottom:3px solid #D4A017;padding:0 0 8px;">
            <p style="margin:0;font-size:18px;font-weight:bold;color:#CC0000;">&#128221; &iquest;C&oacute;mo registrarse?</p>
        </td>
    </tr>
</table>

<p style="color:#4A4A4A;margin:0 0 12px;line-height:1.6;font-size:15px;">
    Ingrese a la siguiente direcci&oacute;n y complete el formulario:
</p>
<p style="margin:0 0 20px;text-align:center;">
    <a href="<?= $regUrl ?>" style="display:inline-block;background:#CC0000;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-size:16px;font-weight:bold;">Registrar mi comercio</a>
</p>

<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 20px;">
    <tr>
        <td style="padding:8px 0 8px 0;">
            <p style="margin:0;font-size:15px;color:#2D2D2D;line-height:1.7;">
                <span style="display:inline-block;width:28px;height:28px;background:#CC0000;color:#ffffff;border-radius:50%;text-align:center;line-height:28px;font-weight:bold;font-size:14px;margin-right:8px;">1</span>
                Ingrese a <a href="<?= $regUrl ?>" style="color:#CC0000;text-decoration:underline;font-weight:bold;"><?= $regUrl ?></a>
            </p>
        </td>
    </tr>
    <tr>
        <td style="padding:8px 0 8px 0;">
            <p style="margin:0;font-size:15px;color:#2D2D2D;line-height:1.7;">
                <span style="display:inline-block;width:28px;height:28px;background:#CC0000;color:#ffffff;border-radius:50%;text-align:center;line-height:28px;font-weight:bold;font-size:14px;margin-right:8px;">2</span>
                Cree su cuenta con email y contrase&ntilde;a
            </p>
        </td>
    </tr>
    <tr>
        <td style="padding:8px 0 8px 0;">
            <p style="margin:0;font-size:15px;color:#2D2D2D;line-height:1.7;">
                <span style="display:inline-block;width:28px;height:28px;background:#CC0000;color:#ffffff;border-radius:50%;text-align:center;line-height:28px;font-weight:bold;font-size:14px;margin-right:8px;">3</span>
                Complete la informaci&oacute;n de su negocio
            </p>
        </td>
    </tr>
    <tr>
        <td style="padding:8px 0 8px 0;">
            <p style="margin:0;font-size:15px;color:#2D2D2D;line-height:1.7;">
                <span style="display:inline-block;width:28px;height:28px;background:#CC0000;color:#ffffff;border-radius:50%;text-align:center;line-height:28px;font-weight:bold;font-size:14px;margin-right:8px;">4</span>
                Suba su logo y foto de portada
            </p>
        </td>
    </tr>
    <tr>
        <td style="padding:8px 0 8px 0;">
            <p style="margin:0;font-size:15px;color:#2D2D2D;line-height:1.7;">
                <span style="display:inline-block;width:28px;height:28px;background:#CC0000;color:#ffffff;border-radius:50%;text-align:center;line-height:28px;font-weight:bold;font-size:14px;margin-right:8px;">5</span>
                Seleccione sus categor&iacute;as y fechas especiales
            </p>
        </td>
    </tr>
</table>


<!-- ═══════════════════════════════════════════════════════════
     ESTANDAR: No editar - Datos requeridos (personal, negocio, imagenes, red social, categorias, fechas)
     ═══════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 8px;">
    <tr>
        <td style="border-bottom:3px solid #D4A017;padding:0 0 8px;">
            <p style="margin:0;font-size:18px;font-weight:bold;color:#CC0000;">&#128203; Datos que necesitar&aacute;</p>
        </td>
    </tr>
</table>

<!-- Datos personales -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFFBF5;border:1px solid #E8D5B0;border-radius:8px;margin:0 0 12px;">
    <tr>
        <td style="background:#D4A017;padding:10px 16px;border-radius:8px 8px 0 0;">
            <p style="margin:0;font-size:15px;font-weight:bold;color:#ffffff;">&#128100; DATOS PERSONALES (para crear su cuenta)</p>
        </td>
    </tr>
    <tr>
        <td style="padding:14px 16px;">
            <p style="margin:0 0 4px;color:#4A4A4A;font-size:14px;">&#8226; Su nombre completo</p>
            <p style="margin:0 0 4px;color:#4A4A4A;font-size:14px;">&#8226; Email (ser&aacute; su usuario para acceder a la plataforma)</p>
            <p style="margin:0 0 4px;color:#4A4A4A;font-size:14px;">&#8226; Tel&eacute;fono / WhatsApp</p>
            <p style="margin:0;color:#4A4A4A;font-size:14px;">&#8226; Contrase&ntilde;a</p>
        </td>
    </tr>
</table>

<!-- Nota contrasena -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFF8F0;border:1px solid #D4A017;border-radius:6px;margin:0 0 12px;">
    <tr>
        <td style="padding:12px 16px;">
            <p style="margin:0 0 6px;color:#CC0000;font-size:14px;font-weight:bold;">&#128274; Importante sobre su contrase&ntilde;a:</p>
            <p style="margin:0 0 6px;color:#4A4A4A;font-size:14px;line-height:1.5;">Gu&aacute;rdela en un lugar seguro. Si la olvida, podr&aacute; recuperarla desde:</p>
            <p style="margin:0;font-size:14px;">
                &#128073; <a href="<?= $siteUrl ?>/mi-comercio/olvide-contrasena" style="color:#CC0000;text-decoration:underline;"><?= $siteUrl ?>/mi-comercio/olvide-contrasena</a>
            </p>
        </td>
    </tr>
</table>

<!-- Informacion del negocio -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFFBF5;border:1px solid #E8D5B0;border-radius:8px;margin:0 0 12px;">
    <tr>
        <td style="background:#D4A017;padding:10px 16px;border-radius:8px 8px 0 0;">
            <p style="margin:0;font-size:15px;font-weight:bold;color:#ffffff;">&#127978; INFORMACI&Oacute;N DE SU NEGOCIO</p>
        </td>
    </tr>
    <tr>
        <td style="padding:14px 16px;">
            <p style="margin:0 0 4px;color:#4A4A4A;font-size:14px;">&#8226; Nombre del comercio</p>
            <p style="margin:0 0 4px;color:#4A4A4A;font-size:14px;">&#8226; Descripci&oacute;n de su negocio (<?= $descNegocio ?>)</p>
            <p style="margin:0 0 4px;color:#4A4A4A;font-size:14px;">&#8226; WhatsApp de contacto para sus clientes</p>
            <p style="margin:0 0 4px;color:#4A4A4A;font-size:14px;">&#8226; Tel&eacute;fono fijo</p>
            <p style="margin:0 0 4px;color:#4A4A4A;font-size:14px;">&#8226; Email del comercio</p>
            <p style="margin:0 0 4px;color:#4A4A4A;font-size:14px;">&#8226; Sitio web o enlace a su red social</p>
            <p style="margin:0;color:#4A4A4A;font-size:14px;">&#8226; Direcci&oacute;n f&iacute;sica (si tiene punto de venta o retiro)</p>
        </td>
    </tr>
</table>

<!-- Imagenes -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFFBF5;border:1px solid #E8D5B0;border-radius:8px;margin:0 0 6px;">
    <tr>
        <td style="background:#D4A017;padding:10px 16px;border-radius:8px 8px 0 0;">
            <p style="margin:0;font-size:15px;font-weight:bold;color:#ffffff;">&#128444; IM&Aacute;GENES (JPG o PNG, m&aacute;ximo 2 MB cada una)</p>
        </td>
    </tr>
    <tr>
        <td style="padding:14px 16px;">
            <p style="margin:0 0 6px;color:#4A4A4A;font-size:14px;">El Plan Gratuito permite subir 2 im&aacute;genes:</p>
            <p style="margin:0 0 4px;color:#4A4A4A;font-size:14px;">&#8226; <strong>Logo de su negocio</strong> &mdash; tama&ntilde;o ideal: 800 &times; 800 px</p>
            <p style="margin:0;color:#4A4A4A;font-size:14px;">&#8226; <strong>Foto de portada</strong> &mdash; tama&ntilde;o ideal: 1200 &times; 400 px (puede ser una foto de sus productos)</p>
        </td>
    </tr>
</table>

<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFF5F5;border:1px solid #E8B0B0;border-radius:6px;margin:0 0 12px;">
    <tr>
        <td style="padding:10px 16px;">
            <p style="margin:0;color:#CC0000;font-size:13px;font-weight:bold;">&#9888;&#65039; Importante: Solo se publicar&aacute;n im&aacute;genes de productos o servicios. No se aceptar&aacute;n fotos en las que aparezcan personas.</p>
        </td>
    </tr>
</table>

<!-- Red social -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFFBF5;border:1px solid #E8D5B0;border-radius:8px;margin:0 0 12px;">
    <tr>
        <td style="background:#D4A017;padding:10px 16px;border-radius:8px 8px 0 0;">
            <p style="margin:0;font-size:15px;font-weight:bold;color:#ffffff;">&#128241; RED SOCIAL</p>
        </td>
    </tr>
    <tr>
        <td style="padding:14px 16px;">
            <p style="margin:0;color:#4A4A4A;font-size:14px;line-height:1.6;">El Plan Gratuito permite incluir 1 red social. Seleccione la que m&aacute;s use (Facebook, Instagram, TikTok, YouTube u otra) e ingrese el enlace de su perfil.</p>
        </td>
    </tr>
</table>

<!-- Categorias -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFFBF5;border:1px solid #E8D5B0;border-radius:8px;margin:0 0 12px;">
    <tr>
        <td style="background:#D4A017;padding:10px 16px;border-radius:8px 8px 0 0;">
            <p style="margin:0;font-size:15px;font-weight:bold;color:#ffffff;">&#128194; CATEGOR&Iacute;AS</p>
        </td>
    </tr>
    <tr>
        <td style="padding:14px 16px;">
            <p style="margin:0;color:#4A4A4A;font-size:14px;line-height:1.6;">En el formulario podr&aacute; seleccionar las categor&iacute;as que correspondan a su negocio y marcar su categor&iacute;a principal.</p>
        </td>
    </tr>
</table>

<!-- Fechas especiales -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFFBF5;border:1px solid #E8D5B0;border-radius:8px;margin:0 0 24px;">
    <tr>
        <td style="background:#D4A017;padding:10px 16px;border-radius:8px 8px 0 0;">
            <p style="margin:0;font-size:15px;font-weight:bold;color:#ffffff;">&#127873; FECHAS ESPECIALES</p>
        </td>
    </tr>
    <tr>
        <td style="padding:14px 16px;">
            <p style="margin:0;color:#4A4A4A;font-size:14px;line-height:1.6;">El formulario tambi&eacute;n le permitir&aacute; seleccionar para qu&eacute; fechas u ocasiones ofrece productos especiales (San Valent&iacute;n, D&iacute;a de la Mujer, D&iacute;a de la Madre, Cumplea&ntilde;os, Navidad, entre otras). Esto nos ayuda a destacar su negocio cuando las personas busquen regalos para esas fechas.</p>
        </td>
    </tr>
</table>


<!-- ═══════════════════════════════════════════════════════════
     ESTANDAR: No editar - Politicas obligatorias
     ═══════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 8px;">
    <tr>
        <td style="border-bottom:3px solid #CC0000;padding:0 0 8px;">
            <p style="margin:0;font-size:18px;font-weight:bold;color:#CC0000;">&#9888;&#65039; Lectura Obligatoria Antes de Registrarse</p>
        </td>
    </tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFF5F5;border:2px solid #CC0000;border-radius:8px;margin:0 0 24px;">
    <tr>
        <td style="padding:20px;">
            <p style="margin:0 0 14px;color:#CC0000;font-size:14px;line-height:1.6;">
                Para completar su registro es <strong>OBLIGATORIO</strong> leer cada uno de los siguientes documentos. El formulario le pedir&aacute; que marque expresamente su decisi&oacute;n en dos casillas por cada pol&iacute;tica: &ldquo;Acepto&rdquo; o &ldquo;Rechazo&rdquo;. Su decisi&oacute;n quedar&aacute; registrada como evidencia formal.
            </p>
            <p style="margin:0 0 6px;color:#CC0000;font-size:14px;">&#128209; 1. <a href="<?= $siteUrl ?>/terminos" style="color:#CC0000;text-decoration:underline;font-weight:bold;">T&eacute;rminos y Condiciones</a></p>
            <p style="margin:0 0 6px;color:#CC0000;font-size:14px;">&#128209; 2. <a href="<?= $siteUrl ?>/privacidad" style="color:#CC0000;text-decoration:underline;font-weight:bold;">Pol&iacute;tica de Privacidad</a></p>
            <p style="margin:0 0 6px;color:#CC0000;font-size:14px;">&#128209; 3. <a href="<?= $siteUrl ?>/contenidos" style="color:#CC0000;text-decoration:underline;font-weight:bold;">Pol&iacute;tica de Contenidos</a></p>
            <p style="margin:0 0 6px;color:#CC0000;font-size:14px;">&#128209; 4. <a href="<?= $siteUrl ?>/derechos" style="color:#CC0000;text-decoration:underline;font-weight:bold;">Ejercicio de Derechos</a></p>
            <p style="margin:0 0 14px;color:#CC0000;font-size:14px;">&#128209; 5. <a href="<?= $siteUrl ?>/cookies" style="color:#CC0000;text-decoration:underline;font-weight:bold;">Pol&iacute;tica de Cookies</a></p>
            <p style="margin:0;color:#CC0000;font-size:14px;font-weight:bold;">&#128683; El rechazo de cualquiera de estas pol&iacute;ticas impide la publicaci&oacute;n de su negocio en la plataforma.</p>
        </td>
    </tr>
</table>


<!-- ═══════════════════════════════════════════════════════════
     ESTANDAR: No editar - Que pasa despues
     ═══════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 8px;">
    <tr>
        <td style="border-bottom:3px solid #D4A017;padding:0 0 8px;">
            <p style="margin:0;font-size:18px;font-weight:bold;color:#CC0000;">&#128203; &iquest;Qu&eacute; pasa despu&eacute;s?</p>
        </td>
    </tr>
</table>
<p style="color:#4A4A4A;margin:0 0 20px;line-height:1.7;font-size:15px;">
    Una vez que complete el formulario con toda su informaci&oacute;n, nuestro equipo revisar&aacute; los datos ingresados. Si se ajustan a los t&eacute;rminos y condiciones de la plataforma, su negocio ser&aacute; publicado y le notificaremos por email.
</p>


<!-- ═══════════════════════════════════════════════════════════
     ESTANDAR: No editar - Edicion de publicacion
     ═══════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 8px;">
    <tr>
        <td style="border-bottom:3px solid #D4A017;padding:0 0 8px;">
            <p style="margin:0;font-size:18px;font-weight:bold;color:#CC0000;">&#9998;&#65039; Edici&oacute;n de su publicaci&oacute;n</p>
        </td>
    </tr>
</table>
<p style="color:#4A4A4A;margin:0 0 8px;line-height:1.7;font-size:15px;">
    Una vez publicado su negocio, podr&aacute; editar y actualizar su informaci&oacute;n en cualquier momento ingresando a:
</p>
<p style="margin:0 0 8px;">
    &#128073; <a href="<?= $siteUrl ?>/mi-comercio/" style="color:#CC0000;text-decoration:underline;font-weight:bold;"><?= $siteUrl ?>/mi-comercio/</a>
</p>
<p style="color:#4A4A4A;margin:0 0 24px;line-height:1.7;font-size:15px;">
    Cada vez que realice cambios, estos ser&aacute;n revisados por nuestro equipo. La actualizaci&oacute;n ser&aacute; aceptada o rechazada seg&uacute;n cumpla con los t&eacute;rminos de uso.
</p>


<!-- ═══════════════════════════════════════════════════════════
     ESTANDAR: No editar - Canales de contacto
     ═══════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="0" cellspacing="0" style="background:#FFF8F0;border:1px solid #E8D5B0;border-radius:8px;margin:0 0 24px;">
    <tr>
        <td style="padding:16px 20px;">
            <p style="margin:0 0 8px;font-size:15px;font-weight:bold;color:#CC0000;">&#128172; &iquest;Tiene dudas?</p>
            <p style="margin:0 0 4px;color:#4A4A4A;font-size:14px;line-height:1.6;">Puede responder directamente a este correo o escribirnos a trav&eacute;s de:</p>
            <p style="margin:0 0 4px;color:#4A4A4A;font-size:14px;">&#128233; Email: <a href="mailto:contacto@regalospurranque.cl" style="color:#CC0000;">contacto@regalospurranque.cl</a></p>
            <p style="margin:0;color:#4A4A4A;font-size:14px;">&#127760; Web: <a href="<?= $siteUrl ?>/contacto" style="color:#CC0000;"><?= $siteUrl ?>/contacto</a></p>
        </td>
    </tr>
</table>


<!-- ═══════════════════════════════════════════════════════════
     ESTANDAR: No editar - Firma profesional centrada con logo
     ═══════════════════════════════════════════════════════════ -->
<table width="100%" cellpadding="0" cellspacing="0" style="border-top:2px solid #D4A017;margin:0;">
    <tr>
        <td style="padding:20px 0 0;text-align:center;">
            <p style="margin:0 0 4px;color:#4A4A4A;font-size:15px;line-height:1.6;">&iexcl;Bienvenida/o a la comunidad de Regalos Purranque! &#127873;</p>
            <p style="margin:0 0 4px;color:#4A4A4A;font-size:15px;">Saludos cordiales,</p>
            <p style="margin:0 0 8px;color:#2D2D2D;font-size:16px;font-weight:bold;">Equipo Regalos Purranque</p>
            <?php if (!empty($logoUrl)): ?>
            <p style="margin:0 0 8px;">
                <img src="<?= $logoUrl ?>" alt="<?= htmlspecialchars($siteName) ?>" width="120" style="max-width:120px;height:auto;">
            </p>
            <?php endif; ?>
            <p style="margin:0 0 4px;">
                <a href="<?= $siteUrl ?>" style="color:#CC0000;text-decoration:underline;font-weight:bold;"><?= $siteUrl ?></a>
            </p>
        </td>
    </tr>
</table>

<p style="color:#999999;font-size:12px;margin:16px 0 0;line-height:1.5;text-align:center;">
    &#128231; Si no encuentra este correo en su bandeja de entrada, revise su carpeta de Spam o Correo no deseado.
</p>
