<?php
/**
 * Lista de productos de un comercio - Admin
 * Variables: $comercio, $productos, $totalProductos, $maxProductos, $plan
 */
?>

<div class="admin-breadcrumb">
    <a href="<?= url('/admin') ?>">Dashboard</a>
    <span>/</span>
    <a href="<?= url('/admin/comercios') ?>">Comercios</a>
    <span>/</span>
    <a href="<?= url('/admin/comercios/editar/' . $comercio['id']) ?>"><?= e($comercio['nombre']) ?></a>
    <span>/</span>
    <span>Productos</span>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.75rem">
    <div>
        <h2 style="margin:0">Productos de <?= e($comercio['nombre']) ?></h2>
        <p style="color:var(--color-gray);font-size:var(--font-size-sm);margin:0.25rem 0 0">
            <?= $totalProductos ?> de <?= $maxProductos ?> productos (Plan: <?= e($plan['nombre'] ?? 'Freemium') ?>)
        </p>
    </div>
    <div style="display:flex;gap:0.5rem">
        <a href="<?= url('/admin/comercios') ?>" class="btn btn--outline">&larr; Comercios</a>
        <?php if ($maxProductos === 0 || $totalProductos < $maxProductos): ?>
            <a href="<?= url('/admin/comercios/' . $comercio['id'] . '/productos/crear') ?>" class="btn btn--primary">+ Agregar producto</a>
        <?php else: ?>
            <span class="btn btn--outline" style="opacity:0.5;cursor:not-allowed">Limite del plan alcanzado</span>
        <?php endif; ?>
    </div>
</div>

<div class="admin-card">
    <div class="admin-card__body" style="padding:0">
        <?php if (!empty($productos)): ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th style="width:60px">Imagen</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th style="width:80px">Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $p): ?>
                        <tr>
                            <td>
                                <?php if (!empty($p['imagen'])): ?>
                                    <img src="<?= asset('img/productos/' . $comercio['id'] . '/thumbs/' . $p['imagen']) ?>"
                                         alt="<?= e($p['nombre']) ?>"
                                         style="width:50px;height:50px;object-fit:cover;border-radius:4px"
                                         loading="lazy"
                                         onerror="this.src='<?= asset('img/productos/' . $comercio['id'] . '/' . $p['imagen']) ?>'">
                                <?php else: ?>
                                    <div style="width:50px;height:50px;background:var(--color-bg);border-radius:4px;display:flex;align-items:center;justify-content:center;color:var(--color-gray)">&#127991;</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= e($p['nombre']) ?></strong>
                                <?php if (!empty($p['descripcion'])): ?>
                                    <br><small style="color:var(--color-gray)"><?= e(mb_substr($p['descripcion'], 0, 80)) ?><?= mb_strlen($p['descripcion']) > 80 ? '...' : '' ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['precio']): ?>
                                    <strong>$ <?= number_format($p['precio'], 0, ',', '.') ?></strong>
                                <?php else: ?>
                                    <span style="color:var(--color-gray)">Sin precio</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox"
                                           <?= $p['activo'] ? 'checked' : '' ?>
                                           data-toggle-url="<?= url('/admin/comercios/' . $comercio['id'] . '/productos/toggle/' . $p['id']) ?>">
                                    <span class="toggle-switch__slider"></span>
                                </label>
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <a href="<?= url('/admin/comercios/' . $comercio['id'] . '/productos/editar/' . $p['id']) ?>"
                                       class="btn btn--outline btn--xs">Editar</a>
                                    <form method="POST"
                                          action="<?= url('/admin/comercios/' . $comercio['id'] . '/productos/eliminar/' . $p['id']) ?>"
                                          style="display:inline"
                                          onsubmit="return confirm('¿Eliminar el producto \'<?= e($p['nombre']) ?>\'?')">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn--danger btn--xs">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state__icon">&#127991;</div>
                <div class="empty-state__title">Sin productos</div>
                <div class="empty-state__text">Este comercio aun no tiene productos.</div>
            </div>
        <?php endif; ?>
    </div>
</div>
