<?php
/**
 * Admin - Listado de noticias
 * Variables: $noticias, $currentPage, $totalPages, $total, $filters
 */
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <span>Noticias</span>
</div>

<h2>Noticias <small style="color:var(--color-gray);font-weight:400">(<?= number_format($total) ?>)</small></h2>

<!-- Toolbar -->
<div class="toolbar">
    <a href="<?= url('/admin/noticias/crear') ?>" class="btn btn--primary btn--sm">+ Nueva noticia</a>

    <div class="toolbar__separator"></div>

    <form method="GET" action="<?= url('/admin/noticias') ?>" class="toolbar__group" style="gap:0.5rem">
        <input type="text"
               name="q"
               class="form-control"
               placeholder="Buscar por titulo..."
               value="<?= e($filters['q'] ?? '') ?>"
               style="width:220px;padding:0.4rem 0.75rem;font-size:0.875rem">

        <select name="estado" class="form-control" style="width:140px;padding:0.4rem 0.75rem;font-size:0.875rem">
            <option value="">Todos los estados</option>
            <option value="1" <?= ($filters['estado'] ?? '') === '1' ? 'selected' : '' ?>>Activas</option>
            <option value="0" <?= ($filters['estado'] ?? '') === '0' ? 'selected' : '' ?>>Inactivas</option>
        </select>

        <button type="submit" class="btn btn--outline btn--sm">Filtrar</button>

        <?php if (!empty($filters['q']) || ($filters['estado'] ?? '') !== ''): ?>
            <a href="<?= url('/admin/noticias') ?>" class="btn btn--outline btn--sm">Limpiar</a>
        <?php endif; ?>
    </form>
</div>

<!-- Tabla de noticias -->
<div class="admin-card">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Titulo</th>
                    <th>Autor</th>
                    <th>Fecha publicacion</th>
                    <th>Destacada</th>
                    <th>Activa</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($noticias)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;padding:2rem;color:var(--color-gray)">
                            No se encontraron noticias.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($noticias as $noticia): ?>
                        <tr>
                            <td>
                                <?php if (!empty($noticia['imagen'])): ?>
                                    <img src="<?= asset('img/noticias/' . $noticia['imagen']) ?>"
                                         alt="<?= e($noticia['titulo']) ?>"
                                         style="width:48px;height:48px;object-fit:cover;border-radius:4px">
                                <?php else: ?>
                                    <div style="width:48px;height:48px;background:var(--color-light);border-radius:4px;display:flex;align-items:center;justify-content:center;color:var(--color-gray);font-size:1.25rem">
                                        &#128247;
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= e(truncate($noticia['titulo'], 50)) ?></strong>
                                <br><small style="color:var(--color-gray)">/noticia/<?= e($noticia['slug']) ?></small>
                            </td>
                            <td><?= e($noticia['autor'] ?? '—') ?></td>
                            <td>
                                <?php if (!empty($noticia['fecha_publicacion'])): ?>
                                    <?= fecha_es($noticia['fecha_publicacion'], 'd/m/Y H:i') ?>
                                <?php else: ?>
                                    <span style="color:var(--color-gray)">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center">
                                <?php if ($noticia['destacada']): ?>
                                    <span style="color:#f59e0b;font-size:1.25rem" title="Destacada">&#9733;</span>
                                <?php else: ?>
                                    <span style="color:#d1d5db;font-size:1.25rem" title="No destacada">&#9734;</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox"
                                           <?= $noticia['activo'] ? 'checked' : '' ?>
                                           data-toggle-url="<?= url('/admin/noticias/toggle/' . $noticia['id']) ?>">
                                    <span class="toggle-switch__slider"></span>
                                </label>
                            </td>
                            <td>
                                <div class="admin-table__actions">
                                    <a href="<?= url('/admin/noticias/editar/' . $noticia['id']) ?>"
                                       class="btn btn--outline btn--sm"
                                       title="Editar">Editar</a>
                                    <a href="<?= url('/noticia/' . $noticia['slug']) ?>"
                                       class="btn btn--outline btn--sm"
                                       target="_blank"
                                       title="Ver en sitio">Ver</a>
                                    <button type="button"
                                            class="btn btn--danger btn--sm"
                                            data-delete-url="<?= url('/admin/noticias/eliminar/' . $noticia['id']) ?>"
                                            data-delete-name="<?= e($noticia['titulo']) ?>"
                                            title="Eliminar">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <div class="admin-págination">
            <div class="admin-págination__info">
                Mostrando página <?= $currentPage ?> de <?= $totalPages ?> (<?= number_format($total) ?> resultados)
            </div>
            <div class="admin-págination__links">
                <?php
                $baseUrl    = '/admin/noticias';
                $queryParams = array_filter($filters, fn($v) => $v !== '');
                include BASE_PATH . '/views/partials/págination.php';
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>
