<h2 style="margin:0 0 16px;color:#1e293b;font-size:20px;">Hemos recibido tu mensaje</h2>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Hola <strong><?= htmlspecialchars($datos['nombre']) ?></strong>,
</p>

<p style="color:#475569;margin:0 0 16px;line-height:1.6;">
    Hemos recibido tu mensaje sobre: &laquo;<?= htmlspecialchars($datos['asunto']) ?>&raquo;
</p>

<p style="color:#475569;margin:0 0 20px;line-height:1.6;">
    Nuestro equipo lo revisar&aacute; y te responderemos a la brevedad.
</p>

<p style="color:#94a3b8;font-size:13px;margin:0 0 12px;line-height:1.5;">
    Este es un correo autom&aacute;tico, no es necesario responder.
</p>

<p style="color:#94a3b8;font-size:13px;margin:0;line-height:1.5;">
    📧 Si no encuentras este correo en tu bandeja de entrada, revisa tu carpeta de Spam o Correo no deseado. El mensaje puede tardar unos minutos en llegar.
</p>
