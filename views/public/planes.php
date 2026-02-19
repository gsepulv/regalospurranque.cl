<?php
/**
 * Página pública de planes — Próximamente
 */
?>

<section class="section">
    <div class="container" style="max-width:640px;text-align:center">

        <div style="margin:2rem 0 1.5rem">
            <span style="font-size:3rem">📋</span>
            <h1 style="font-size:1.75rem;margin:0.75rem 0 0.5rem">Nuestros planes</h1>
            <p style="color:#6B7280;font-size:1.05rem">
                Elige el plan que mejor se adapte a tu negocio.
            </p>
        </div>

        <!-- Planes -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:2rem">

            <div style="background:var(--color-white);border-radius:12px;padding:1.25rem;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
                <span style="font-size:1.75rem">🆓</span>
                <h3 style="margin:0.5rem 0 0.25rem;font-size:1.1rem">Freemium</h3>
                <p style="color:#6B7280;font-size:0.85rem;margin:0">Gratuito</p>
            </div>

            <div style="background:var(--color-white);border-radius:12px;padding:1.25rem;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
                <span style="font-size:1.75rem">✅</span>
                <h3 style="margin:0.5rem 0 0.25rem;font-size:1.1rem">Básico</h3>
                <p style="color:#6B7280;font-size:0.85rem;margin:0">Desde $2.990/mes</p>
            </div>

            <div style="background:var(--color-white);border-radius:12px;padding:1.25rem;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
                <span style="font-size:1.75rem">⭐</span>
                <h3 style="margin:0.5rem 0 0.25rem;font-size:1.1rem">Premium</h3>
                <p style="color:#6B7280;font-size:0.85rem;margin:0">Desde $7.990/mes</p>
            </div>

            <div style="background:var(--color-white);border-radius:12px;padding:1.25rem;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
                <span style="font-size:1.75rem">🏆</span>
                <h3 style="margin:0.5rem 0 0.25rem;font-size:1.1rem">Sponsor</h3>
                <p style="color:#6B7280;font-size:0.85rem;margin:0">Desde $15.990/mes</p>
            </div>

        </div>

        <!-- Próximamente -->
        <div style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:12px;padding:1.25rem;margin-bottom:2rem">
            <p style="margin:0;font-size:0.95rem;color:#92400E">
                🚧 <strong>Próximamente</strong> publicaremos el detalle completo de cada plan con todos sus beneficios.
            </p>
        </div>

        <a href="<?= url('/') ?>" class="btn btn--primary" style="padding:0.65rem 2rem">
            Volver al inicio
        </a>

    </div>
</section>
