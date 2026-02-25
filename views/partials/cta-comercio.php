<?php
/**
 * CTA section - "¿Tienes un comercio en Purranque?"
 * Se muestra antes del footer en todas las páginas públicas
 */
?>
<section class="cta-section">
    <div class="container" style="text-align:center;max-width:800px;">
        <p style="margin:0 0 .3rem;font-size:2rem;line-height:1;">&#127978;</p>
        <h2 style="color:#D4A017;margin-bottom:.75rem;">&iquest;Tienes un comercio en Purranque?</h2>
        <p style="color:#D4A017;font-weight:600;font-size:1rem;margin:0 0 .75rem;">
            Reg&iacute;strate gratis por 30 d&iacute;as y obt&eacute;n:
        </p>
        <div style="display:inline-block;text-align:left;margin:0 auto 1.5rem;line-height:1.9;font-size:.9rem;color:rgba(255,255,255,.9);">
            <span style="color:#38a169;">&#10004;</span> P&aacute;gina exclusiva de tu negocio en la plataforma<br>
            <span style="color:#38a169;">&#10004;</span> Logo (800&times;800 px) y foto de portada (1200&times;400 px)<br>
            <span style="color:#38a169;">&#10004;</span> Bot&oacute;n directo a tu WhatsApp<br>
            <span style="color:#38a169;">&#10004;</span> Enlace a Google Maps<br>
            <span style="color:#38a169;">&#10004;</span> 1 red social (Facebook, Instagram, TikTok, etc.)<br>
            <span style="color:#38a169;">&#10004;</span> Visibilidad en fechas especiales (D&iacute;a de la Madre, Navidad, etc.)
        </div>
        <div style="display:flex;flex-wrap:wrap;justify-content:center;align-items:center;gap:.75rem;margin-bottom:1rem;">
            <a href="<?= url('/registrar-comercio') ?>" class="btn--cta" style="background:#CC0000;border:2px solid #CC0000;">
                Registrar mi comercio
            </a>
            <a href="<?= url('/mi-comercio/login') ?>" class="btn--cta" style="background:transparent;border:2px solid #D4A017;color:#D4A017;font-size:.9rem;padding:.6rem 1.25rem;">
                Acceder a mi comercio
            </a>
            <a href="<?= url('/contacto') ?>" class="btn--cta" style="background:#38a169;border:2px solid #38a169;font-size:.9rem;padding:.6rem 1.25rem;">
                Cont&aacute;ctanos
            </a>
        </div>
        <a href="<?= url('/mi-comercio/olvide-contrasena') ?>" style="color:rgba(255,255,255,.6);font-size:.8rem;text-decoration:underline;text-underline-offset:3px;">
            &iquest;Olvidaste tu contrase&ntilde;a?
        </a>
    </div>
</section>
