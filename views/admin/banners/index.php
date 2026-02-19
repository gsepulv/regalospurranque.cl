<?php
/**
 * Admin - Listado de banners
 * Variables: $banners, $filters
 */
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <span>Banners</span>
</div>

<h2>Banners</h2>

<!-- Toolbar -->
<div class="toolbar">
    <a href="<?= url('/admin/banners/crear') ?>" class="btn btn--primary btn--sm">+ Nuevo banner</a>

    <div class="toolbar__separator"></div>

    <form method="GET" action="<?= url('/admin/banners') ?>" class="toolbar__group" style="gap:0.5rem">
        <select name="tipo" class="form-control" style="width:170px;padding:0.4rem 0.75rem;font-size:0.875rem">
            <option value="">Todos los tipos</option>
            <option value="hero" <?= ($filters['tipo'] ?? '') === 'hero' ? 'selected' : '' ?>>Hero</option>
            <option value="sidebar" <?= ($filters['tipo'] ?? '') === 'sidebar' ? 'selected' : '' ?>>Sidebar</option>
            <option value="entre_comercios" <?= ($filters['tipo'] ?? '') === 'entre_comercios' ? 'selected' : '' ?>>Entre comercios</option>
            <option value="footer" <?= ($filters['tipo'] ?? '') === 'footer' ? 'selected' : '' ?>>Footer</option>
        </select>

        <select name="estado" class="form-control" style="width:140px;padding:0.4rem 0.75rem;font-size:0.875rem">
            <option value="">Todos los estados</option>
            <option value="1" <?= ($filters['estado'] ?? '') === '1' ? 'selected' : '' ?>>Activos</option>
            <option value="0" <?= ($filters['estado'] ?? '') === '0' ? 'selected' : '' ?>>Inactivos</option>
        </select>

        <button type="submit" class="btn btn--outline btn--sm">Filtrar</button>

        <?php if (!empty($filters['tipo']) || ($filters['estado'] ?? '') !== ''): ?>
            <a href="<?= url('/admin/banners') ?>" class="btn btn--outline btn--sm">Limpiar</a>
        <?php endif; ?>
    </form>
</div>

<!-- Tabla de banners -->
<div class="admin-card">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Titulo</th>
                    <th>Tipo</th>
                    <th>Posicion</th>
                    <th>Comercio</th>
                    <th>Clicks</th>
                    <th>Impresiones</th>
                    <th>CTR%</th>
                    <th>Activo</th>
                    <th>Vigente</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($banners)): ?>
                    <tr>
                        <td colspan="11" style="text-align:center;padding:2rem;color:var(--color-gray)">
                            No se encontraron banners.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($banners as $banner): ?>
                        <?php
                        $ctr = $banner['impresiones'] > 0
                            ? round(($banner['clicks'] / $banner['impresiones']) * 100, 2)
                            : 0;

                        // Determinar vigencia por fechas
                        $hoy = date('Y-m-d');
                        $vigente = true;
                        if (!empty($banner['fecha_inicio']) && $hoy < $banner['fecha_inicio']) {
                            $vigente = false;
                        }
                        if (!empty($banner['fecha_fin']) && $hoy > $banner['fecha_fin']) {
                            $vigente = false;
                        }

                        // Badge de tipo
                        $tipoBadges = [
                            'hero'             => 'badge--primary',
                            'sidebar'          => 'badge--warning',
                            'entre_comercios'  => 'badge--success',
                            'footer'           => 'badge--danger',
                        ];
                        $tipoBadge = $tipoBadges[$banner['tipo']] ?? '';
                        ?>
                        <tr>
                            <td>
                                <?php if (!empty($banner['imagen'])): ?>
                                    <img src="<?= asset('img/banners/' . $banner['imagen']) ?>"
                                         alt="<?= e($banner['titulo']) ?>"
                                         style="width:80px;height:40px;object-fit:cover;border-radius:4px">
                                <?php else: ?>
                                    <div style="width:80px;height:40px;background:var(--color-light);border-radius:4px;display:flex;align-items:center;justify-content:center;color:var(--color-gray);font-size:0.75rem">
                                        Sin imagen
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= e($banner['titulo'] ?: '(Sin titulo)') ?></strong>
                                <?php if (!empty($banner['url'])): ?>
                                    <br><small style="color:var(--color-gray)"><?= e(truncate($banner['url'], 40)) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $tipoBadge ?>"><?= e($banner['tipo']) ?></span>
                            </td>
                            <td><?= e($banner['posicion'] ?: '—') ?></td>
                            <td><?= e($banner['comercio_nombre'] ?? '—') ?></td>
                            <td><?= number_format($banner['clicks']) ?></td>
                            <td><?= number_format($banner['impresiones']) ?></td>
                            <td><?= $ctr ?>%</td>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox"
                                           <?= $banner['activo'] ? 'checked' : '' ?>
                                           data-toggle-url="<?= url('/admin/banners/toggle/' . $banner['id']) ?>">
                                    <span class="toggle-switch__slider"></span>
                                </label>
                            </td>
                            <td>
                                <?php if ($vigente): ?>
                                    <span class="status status--active">Vigente</span>
                                <?php else: ?>
                                    <span class="status status--inactive">No vigente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <a href="<?= url('/admin/banners/editar/' . $banner['id']) ?>"
                                       class="btn btn--outline btn--sm"
                                       title="Editar">Editar</a>
                                    <form method="POST" action="<?= url('/admin/banners/reset/' . $banner['id']) ?>" style="display:inline">
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                                class="btn btn--outline btn--sm"
                                                title="Resetear estadisticas"
                                                onclick="return confirm('¿Resetear estadisticas de este banner?')">Reset stats</button>
                                    </form>
                                    <button type="button"
                                            class="btn btn--danger btn--sm"
                                            data-delete-url="<?= url('/admin/banners/eliminar/' . $banner['id']) ?>"
                                            data-delete-name="<?= e($banner['titulo'] ?: 'Banner #' . $banner['id']) ?>"
                                            title="Eliminar">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
