<?php
/**
 * CTA section - "¿Tienes un comercio en Purranque?"
 * Se muestra antes del footer en todas las páginas públicas
 */
?>
<section class="cta-section">
    <div class="container" style="text-align:center;max-width:800px;">
        <p style="margin:0 0 .3rem;font-size:2rem;line-height:1;">&#127978;</p>
        <h2 style="color:#D4A017;margin-bottom:.4rem;">&iquest;Tienes un comercio en Purranque?</h2>
        <p style="color:rgba(255,255,255,.75);margin:0 auto 1.5rem;max-width:520px;">
            Reg&iacute;strate gratis, administra tu publicaci&oacute;n y aparece cuando la gente busque regalos para cada ocasi&oacute;n especial.
        </p>
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
