<?php
/**
 * Dashboard del comerciante
 * Variables: $comercio, $pendientes, $plan, $usuario, $renovacion, $renovacionesActivas, $planesDisponibles, $metodosPago, $datosBanco
 */
$success = $_SESSION['flash_success'] ?? '';
$error   = $_SESSION['flash_error'] ?? '';
$info    = $_SESSION['flash_info'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error'], $_SESSION['flash_info']);
?>

<section class="section">
    <div class="container" style="max-width:720px">

        <!-- Header -->
        <div style="margin-bottom:1.5rem">
            <h1 style="font-size:1.5rem;margin:0">Mi comercio</h1>
        </div>

        <!-- Mensajes flash -->
        <?php if ($success): ?>
            <div style="background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <?= e($success) ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div style="background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <?= e($error) ?>
            </div>
        <?php endif; ?>
        <?php if ($info): ?>
            <div style="background:#DBEAFE;border:1px solid #BFDBFE;color:#1E40AF;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <?= e($info) ?>
            </div>
        <?php endif; ?>

        <?php if (!$comercio): ?>
            <!-- Sin comercio -->
            <div style="background:var(--color-white);border-radius:12px;padding:2rem;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
                <span style="font-size:3rem">📋</span>
                <h2 style="margin:0.75rem 0 0.5rem;font-size:1.25rem">Aún no tienes un comercio registrado</h2>
                <p style="color:#6B7280">Completa el registro de tu negocio para aparecer en el directorio.</p>
                <a href="<?= url('/registrar-comercio/datos') ?>" class="btn btn--primary" style="margin-top:1rem">
                    Registrar mi comercio
                </a>
            </div>

        <?php else: ?>
            <?php $completitud = \App\Models\Comercio::checkCompletitud($comercio); ?>

            <?php if (!$completitud['completa']): ?>
            <!-- Indicador de completitud -->
            <div style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:12px;padding:1.25rem;margin-bottom:1.25rem">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem">
                    <strong style="font-size:0.95rem">Tu ficha está al <?= $completitud['porcentaje'] ?>%</strong>
                    <span style="font-size:0.8rem;color:#92400E">Completa tu ficha para mayor visibilidad</span>
                </div>
                <div style="background:#FDE68A;border-radius:99px;height:8px;overflow:hidden">
                    <div style="background:#F59E0B;height:100%;width:<?= $completitud['porcentaje'] ?>%;border-radius:99px;transition:width 0.3s"></div>
                </div>
                <ul style="margin:0.75rem 0 0;padding:0;list-style:none;font-size:0.85rem;color:#6B7280">
                    <?php if (!$completitud['items']['descripcion']): ?>
                        <li style="margin-bottom:0.25rem">&#9744; Agrega una descripción de al menos 100 caracteres</li>
                    <?php endif; ?>
                    <?php if (!$completitud['items']['imagen']): ?>
                        <li style="margin-bottom:0.25rem">&#9744; Sube una imagen de portada</li>
                    <?php endif; ?>
                    <?php if (!$completitud['items']['contacto']): ?>
                        <li style="margin-bottom:0.25rem">&#9744; Agrega al menos un dato de contacto (teléfono, WhatsApp o email)</li>
                    <?php endif; ?>
                    <?php if (!$completitud['items']['categoria']): ?>
                        <li style="margin-bottom:0.25rem">&#9744; Selecciona al menos una categoría</li>
                    <?php endif; ?>
                </ul>
                <p style="margin:0.75rem 0 0;font-size:0.8rem;color:#92400E">
                    <strong>Tu ficha no aparece en el directorio hasta completar todos los campos.</strong>
                </p>
                <a href="<?= url('/mi-comercio/editar') ?>" style="display:inline-block;margin-top:0.5rem;color:#D97706;font-weight:600;font-size:0.85rem;text-decoration:none">Completar ficha &rarr;</a>
            </div>
            <?php endif; ?>

            <!-- Estado del comercio -->
            <div style="background:var(--color-white);border-radius:12px;padding:1.25rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);margin-bottom:1.25rem">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:0.75rem">
                    <div>
                        <h2 style="margin:0;font-size:1.25rem"><?= e($comercio['nombre']) ?></h2>
                        <p style="color:#6B7280;margin:0.25rem 0 0;font-size:0.9rem">
                            <?= e($comercio['categorias_nombres'] ?: 'Sin categorías') ?>
                        </p>
                    </div>
                    <div style="display:flex;gap:0.5rem;flex-wrap:wrap">
                        <?php if ($comercio['activo']): ?>
                            <span style="background:#F0FDF4;color:#166534;padding:0.25rem 0.75rem;border-radius:20px;font-size:0.8rem;font-weight:600">✅ Publicado</span>
                        <?php else: ?>
                            <span style="background:#FEF3C7;color:#92400E;padding:0.25rem 0.75rem;border-radius:20px;font-size:0.8rem;font-weight:600">⏳ Pendiente de revisión</span>
                        <?php endif; ?>
                        <span style="background:#F3F4F6;color:#6B7280;padding:0.25rem 0.75rem;border-radius:20px;font-size:0.8rem">
                            <?= e($plan['icono'] ?? '🆓') ?> <?= e($plan['nombre'] ?? 'Freemium') ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Cambios pendientes -->
            <?php if ($pendientes): ?>
                <div style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:12px;padding:1rem;margin-bottom:1.25rem">
                    <p style="margin:0;font-size:0.9rem;color:#92400E">
                        ⏳ <strong>Tienes cambios pendientes de aprobación</strong> enviados el <?= date('d/m/Y H:i', strtotime($pendientes['created_at'])) ?>.
                        Nuestro equipo los revisará pronto.
                    </p>
                </div>
            <?php endif; ?>

            <!-- Renovación de plan -->
            <?php include __DIR__ . '/_renovacion.php'; ?>

            <!-- Resumen rápido -->
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.75rem;margin-bottom:1.25rem">
                <div style="background:var(--color-white);border-radius:12px;padding:1rem;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
                    <span style="font-size:1.5rem"><?= $comercio['logo'] ? '✅' : '❌' ?></span>
                    <p style="margin:0.35rem 0 0;font-size:0.8rem;color:#6B7280">Logo</p>
                </div>
                <div style="background:var(--color-white);border-radius:12px;padding:1rem;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
                    <span style="font-size:1.5rem"><?= $comercio['portada'] ? '✅' : '❌' ?></span>
                    <p style="margin:0.35rem 0 0;font-size:0.8rem;color:#6B7280">Portada</p>
                </div>
                <div style="background:var(--color-white);border-radius:12px;padding:1rem;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
                    <span style="font-size:1.5rem"><?= $comercio['whatsapp'] ? '✅' : '❌' ?></span>
                    <p style="margin:0.35rem 0 0;font-size:0.8rem;color:#6B7280">WhatsApp</p>
                </div>
            </div>

            <!-- Acciones -->
            <div style="display:flex;gap:0.75rem;flex-wrap:wrap">
                <a href="<?= url('/mi-comercio/editar') ?>" class="btn btn--primary" style="flex:1;text-align:center;padding:0.75rem">
                    ✏️ Editar información
                </a>
                <a href="<?= url('/mi-comercio/productos') ?>" class="btn btn--outline" style="flex:1;text-align:center;padding:0.75rem">
                    🏷️ Mis productos
                </a>
                <?php if ($comercio['activo']): ?>
                    <a href="<?= url('/comercio/' . $comercio['slug']) ?>" class="btn btn--outline" style="flex:1;text-align:center;padding:0.75rem" target="_blank">
                        👁️ Ver mi ficha pública
                    </a>
                <?php endif; ?>
                <a href="<?= url('/mi-comercio/perfil') ?>" class="btn btn--outline" style="flex:1;text-align:center;padding:0.75rem">
                    👤 Mi perfil
                </a>
            </div>

            <!-- Upgrade plan -->
            <?php if ($comercio['plan'] === 'freemium'): ?>
                <div style="background:#F0FDF4;border:1px solid #BBF7D0;border-radius:12px;padding:1rem;margin-top:1.25rem">
                    <p style="margin:0;font-size:0.9rem;color:#166534">
                        💡 <strong>¿Quieres más visibilidad?</strong> Con el Plan Básico obtienes 3 fotos, todas las redes, horarios y sello verificado.
                        <a href="<?= url('/planes') ?>" style="color:#166534;font-weight:600">Ver planes →</a>
                    </p>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</section>
