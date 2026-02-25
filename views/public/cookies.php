<?php
/**
 * Política de cookies
 */
?>

<section class="section">
    <div class="container">
        <div class="legal-content">
            <h1>Política de Cookies</h1>
            <p class="legal-updated">Última actualización: 24 de febrero de 2026, 22:00 hrs.</p>

            <h2>1. ¿Qué son las Cookies?</h2>
            <p>Las cookies son pequeños archivos de texto que los sitios web almacenan en su dispositivo (computador, tablet o teléfono) cuando los visita. Se utilizan ampliamente para hacer que los sitios web funcionen de manera más eficiente y para proporcionar información a los propietarios del sitio.</p>

            <h2>2. Cookies que Utilizamos</h2>

            <h3>2.1. Cookies Esenciales (Necesarias)</h3>
            <p>Estas cookies son imprescindibles para el funcionamiento de la plataforma y no pueden ser desactivadas.</p>
            <table class="cookies-table">
                <thead>
                    <tr>
                        <th>Cookie</th>
                        <th>Propósito</th>
                        <th>Duración</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code><?= e(SESSION_NAME) ?></code></td>
                        <td>Identificador de sesión del usuario. Permite mantener la sesión iniciada de comerciantes y administradores.</td>
                        <td>2 horas</td>
                    </tr>
                </tbody>
            </table>

            <h3>2.2. Cookies Funcionales</h3>
            <p>Permiten recordar preferencias del usuario para una mejor experiencia.</p>
            <table class="cookies-table">
                <thead>
                    <tr>
                        <th>Cookie</th>
                        <th>Propósito</th>
                        <th>Duración</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Sesión PHP</td>
                        <td>Mantener la sesión activa, autenticación de comerciantes, token CSRF, mensajes flash</td>
                        <td>2 horas</td>
                    </tr>
                </tbody>
            </table>

            <h3>2.3. Cookies Analíticas</h3>
            <p><?= e(SITE_NAME) ?> no utiliza cookies de análisis de terceros (como Google Analytics). El análisis de tráfico se realiza internamente sin cookies adicionales.</p>

            <h3>2.4. Cookies de Terceros</h3>
            <p><?= e(SITE_NAME) ?> no utiliza cookies de publicidad ni cookies de seguimiento de terceros. No utilizamos ningún CDN que instale cookies (excepto los tiles de OpenStreetMap en el mapa, que no instalan cookies).</p>

            <h2>3. ¿Cómo Controlar las Cookies?</h2>
            <p>Puede controlar y/o eliminar las cookies según desee. La mayoría de los navegadores le permiten:</p>
            <ul>
                <li><strong>Ver cookies:</strong> Consultar qué cookies están almacenadas y eliminarlas individualmente.</li>
                <li><strong>Bloquear cookies:</strong> Configurar su navegador para que bloquee todas o algunas cookies.</li>
                <li><strong>Eliminar cookies:</strong> Borrar todas las cookies almacenadas al cerrar el navegador.</li>
            </ul>
            <p>Para gestionar las cookies en su navegador:</p>
            <ul>
                <li><strong>Chrome:</strong> Configuración > Privacidad y seguridad > Cookies y otros datos de sitios</li>
                <li><strong>Firefox:</strong> Opciones > Privacidad y seguridad > Cookies y datos del sitio</li>
                <li><strong>Safari:</strong> Preferencias > Privacidad > Gestionar datos del sitio web</li>
                <li><strong>Edge:</strong> Configuración > Cookies y permisos del sitio > Cookies y datos almacenados</li>
            </ul>
            <p><strong>Nota:</strong> Si desactiva las cookies esenciales, es posible que algunas funciones de la plataforma no funcionen correctamente.</p>

            <h2>4. Actualizaciones</h2>
            <p>Esta política puede actualizarse periódicamente para reflejar cambios en el uso de cookies. La fecha de última actualización se muestra al inicio de esta página.</p>

            <h2>5. Contacto</h2>
            <p>Para consultas sobre nuestra política de cookies:</p>
            <ul>
                <li>Correo electrónico: <a href="mailto:regalospurranque@gmail.com">regalospurranque@gmail.com</a></li>
            </ul>
            <p>Para más información sobre el tratamiento de datos personales, consulte nuestra <a href="<?= url('/privacidad') ?>">Política de Privacidad</a>.</p>
        </div>
    </div>
</section>
