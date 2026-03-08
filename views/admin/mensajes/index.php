<div class="admin-page">
    <div class="admin-page__header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <h1>Seguimiento de Mensajes</h1>
        <div style="display:flex;gap:8px;">
            <form method="POST" action="<?= url('/admin/mensajes/detectar') ?>" style="display:inline;">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--outline btn--sm"
                        onclick="return confirm('Detectar conversiones automaticamente?')">
                    &#128269; Detectar conversiones
                </button>
            </form>
            <a href="<?= url('/admin/mensajes/dashboard') ?>" class="btn btn--primary btn--sm">
                &#128200; Dashboard
            </a>
        </div>
    </div>

    <?php if (!empty($_SESSION['flash']['success'])): ?>
        <div class="alert alert--success"><?= e($_SESSION['flash']['success']) ?></div>
        <?php unset($_SESSION['flash']['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash']['info'])): ?>
        <div class="alert alert--info"><?= e($_SESSION['flash']['info']) ?></div>
        <?php unset($_SESSION['flash']['info']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash']['error'])): ?>
        <div class="alert alert--danger"><?= e($_SESSION['flash']['error']) ?></div>
        <?php unset($_SESSION['flash']['error']); ?>
    <?php endif; ?>

    <!-- Tabs por estado -->
    <div style="display:flex;gap:4px;margin-bottom:1rem;flex-wrap:wrap;">
        <?php
        $tabs = [
            ''            => ['Todos',       $contadores['todos']],
            'nuevo'       => ['Nuevos',      $contadores['nuevo']],
            'leido'       => ['Leidos',      $contadores['leido']],
            'respondido'  => ['Respondidos', $contadores['respondido']],
            'convertido'  => ['Convertidos', $contadores['convertido']],
            'descartado'  => ['Descartados', $contadores['descartado']],
        ];
        foreach ($tabs as $val => [$label, $count]):
            $active = ($filters['estado'] ?? '') === $val;
            $params = array_filter(array_merge($filters, ['estado' => $val, 'page' => '']));
            $href = url('/admin/mensajes') . ($params ? '?' . http_build_query($params) : '');
        ?>
            <a href="<?= $href ?>"
               class="btn btn--sm <?= $active ? 'btn--primary' : 'btn--outline' ?>">
                <?= $label ?>
                <span style="background:<?= $active ? 'rgba(255,255,255,.3)' : 'var(--gray-200)' ?>;
                       padding:1px 6px;border-radius:10px;font-size:11px;margin-left:4px;">
                    <?= $count ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Filtros -->
    <div class="card" style="margin-bottom:1rem;">
        <div class="card__body" style="padding:12px;">
            <form class="filters-row" method="GET" action="<?= url('/admin/mensajes') ?>" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
                <input type="hidden" name="estado" value="<?= e($filters['estado'] ?? '') ?>">
                <div class="form-group" style="margin:0;">
                    <label style="font-size:12px;">Buscar</label>
                    <input type="text" name="q" class="form-control" placeholder="Nombre o email..."
                           value="<?= e($filters['q'] ?? '') ?>" style="min-width:200px;">
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="font-size:12px;">Desde</label>
                    <input type="date" name="desde" class="form-control" value="<?= e($filters['desde'] ?? '') ?>">
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="font-size:12px;">Hasta</label>
                    <input type="date" name="hasta" class="form-control" value="<?= e($filters['hasta'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn--primary btn--sm">Filtrar</button>
                <a href="<?= url('/admin/mensajes') ?>" class="btn btn--outline btn--sm">Limpiar</a>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <?php if (empty($mensajes)): ?>
        <div class="empty-state">
            <div class="empty-state__icon">&#128233;</div>
            <div class="empty-state__title">Sin mensajes</div>
            <div class="empty-state__text">No se encontraron mensajes con los filtros seleccionados.</div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Asunto</th>
                        <th>Estado</th>
                        <th>Nurturing</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mensajes as $msg): ?>
                        <tr style="<?= $msg['estado'] === 'nuevo' ? 'font-weight:600;' : '' ?>">
                            <td>
                                <small><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></small>
                            </td>
                            <td><?= e($msg['nombre']) ?></td>
                            <td><a href="mailto:<?= e($msg['email']) ?>"><?= e($msg['email']) ?></a></td>
                            <td><?= e(mb_substr($msg['asunto'], 0, 50)) ?></td>
                            <td>
                                <?php
                                $badges = [
                                    'nuevo'      => ['badge--warning', 'Nuevo'],
                                    'leido'      => ['badge--info', 'Leido'],
                                    'respondido' => ['badge--primary', 'Respondido'],
                                    'convertido' => ['badge--success', 'Convertido'],
                                    'descartado' => ['badge--secondary', 'Descartado'],
                                ];
                                $b = $badges[$msg['estado']] ?? ['', $msg['estado']];
                                ?>
                                <span class="badge <?= $b[0] ?>"><?= $b[1] ?></span>
                                <?php if (!empty($msg['comercio_id'])): ?>
                                    <span class="badge badge--success" style="font-size:10px;">&#10003; Registrado</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px;">
                                <?php if (!empty($msg['desuscrito'])): ?>
                                    <span class="badge badge--danger" style="font-size:10px;">Desuscrito</span>
                                <?php elseif (!empty($msg['nurturing_pausado'])): ?>
                                    <span class="badge badge--warning" style="font-size:10px;">Pausado</span>
                                <?php elseif (($msg['recordatorios_enviados'] ?? 0) > 0): ?>
                                    <span style="font-weight:600;"><?= $msg['recordatorios_enviados'] ?>/<?= $maxRec ?? 4 ?></span>
                                <?php elseif (!empty($msg['instrucciones_enviadas'])): ?>
                                    <span style="color:var(--gray-400);">Pendiente</span>
                                <?php else: ?>
                                    <span style="color:var(--gray-300);">--</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= url('/admin/mensajes/' . $msg['id']) ?>" class="btn btn--outline btn--xs">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <?php include BASE_PATH . '/views/partials/pagination.php'; ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
