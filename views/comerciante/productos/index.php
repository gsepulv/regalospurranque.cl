<?php
/**
 * Lista de productos del comerciante
 * Variables: $comercio, $productos, $totalProductos, $maxProductos, $plan
 */
$success = $_SESSION['flash_success'] ?? '';
$error   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<section class="section">
    <div class="container" style="max-width:720px">

        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:0.75rem">
            <div>
                <h1 style="font-size:1.5rem;margin:0">Mis productos</h1>
                <p style="color:#6B7280;margin:0.25rem 0 0;font-size:0.85rem">
                    <?= $totalProductos ?> de <?= $maxProductos ?> productos
                </p>
            </div>
            <div style="display:flex;gap:0.5rem;align-items:center">
                <a href="<?= url('/mi-comercio') ?>" class="btn btn--outline" style="font-size:0.85rem;padding:0.5rem 0.75rem">
                    &larr; Dashboard
                </a>
                <?php if ($totalProductos < $maxProductos): ?>
                    <a href="<?= url('/mi-comercio/productos/crear') ?>" class="btn btn--primary" style="font-size:0.85rem;padding:0.5rem 0.75rem">
                        + Agregar producto
                    </a>
                <?php endif; ?>
            </div>
        </div>

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

        <?php if ($totalProductos >= $maxProductos): ?>
            <div style="background:#FEF3C7;border:1px solid #FDE68A;border-radius:8px;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.85rem;color:#92400E">
                Has alcanzado el límite de <?= $maxProductos ?> productos de tu plan <strong><?= e($plan['nombre'] ?? 'Freemium') ?></strong>.
                <?php if (in_array($comercio['plan'], ['freemium', 'basico'])): ?>
                    <a href="<?= url('/planes') ?>" style="color:#92400E;font-weight:600">Mejora tu plan &rarr;</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($productos)): ?>
            <div style="background:var(--color-white);border-radius:12px;padding:2rem;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,0.08)">
                <span style="font-size:3rem">&#127991;</span>
                <h2 style="margin:0.75rem 0 0.5rem;font-size:1.15rem">Aún no has agregado productos</h2>
                <p style="color:#6B7280;font-size:0.9rem">¡Agrega tu primer producto para que tus clientes vean lo que ofreces!</p>
                <a href="<?= url('/mi-comercio/productos/crear') ?>" class="btn btn--primary" style="margin-top:1rem">
                    + Agregar mi primer producto
                </a>
            </div>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:0.75rem">
                <?php foreach ($productos as $p): ?>
                    <div style="background:var(--color-white);border-radius:12px;padding:1rem;box-shadow:0 2px 8px rgba(0,0,0,0.08);display:flex;gap:1rem;align-items:center">
                        <?php if (!empty($p['imagen'])): ?>
                            <img src="<?= asset('img/productos/' . $comercio['id'] . '/thumbs/' . $p['imagen']) ?>"
                                 alt="<?= e($p['nombre']) ?>"
                                 style="width:64px;height:64px;object-fit:cover;border-radius:8px;flex-shrink:0"
                                 loading="lazy"
                                 onerror="this.src='<?= asset('img/productos/' . $comercio['id'] . '/' . $p['imagen']) ?>'">
                        <?php else: ?>
                            <div style="width:64px;height:64px;background:#F3F4F6;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.5rem;color:#9CA3AF">
                                &#127991;
                            </div>
                        <?php endif; ?>
                        <div style="flex:1;min-width:0">
                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.5rem">
                                <div style="min-width:0">
                                    <strong style="font-size:0.95rem;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($p['nombre']) ?></strong>
                                    <?php if ($p['precio']): ?>
                                        <span style="color:#166534;font-weight:600;font-size:0.9rem">$ <?= number_format($p['precio'], 0, '', '.') ?></span>
                                    <?php endif; ?>
                                </div>
                                <div style="display:flex;gap:0.5rem;flex-shrink:0">
                                    <?php if (!$p['activo']): ?>
                                        <span style="background:#FEF3C7;color:#92400E;padding:0.15rem 0.5rem;border-radius:12px;font-size:0.75rem">Inactivo</span>
                                    <?php endif; ?>
                                    <a href="<?= url('/mi-comercio/productos/editar/' . $p['id']) ?>" style="color:#3B82F6;font-size:0.85rem;text-decoration:none" title="Editar">&#9998;</a>
                                    <form method="POST" action="<?= url('/mi-comercio/productos/eliminar/' . $p['id']) ?>" style="margin:0" onsubmit="return confirm('¿Eliminar este producto?')">
                                        <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                                        <button type="submit" style="background:none;border:none;color:#EF4444;cursor:pointer;font-size:0.85rem;padding:0" title="Eliminar">&#128465;</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>
