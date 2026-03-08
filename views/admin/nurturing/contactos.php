<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="<?= url('/admin/nurturing') ?>">Nurturing</a>
        <span>/</span>
        <span>Contactos</span>
    </div>

    <div class="admin-page__header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <h1>Contactos en nurturing</h1>
        <div style="display:flex;gap:4px;">
            <form method="POST" action="<?= url('/admin/nurturing/masiva') ?>" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="accion" value="pausar_todos">
                <button type="submit" class="btn btn--outline btn--xs"
                        onclick="return confirm('Pausar todos los contactos en cola?')">Pausar todos</button>
            </form>
            <form method="POST" action="<?= url('/admin/nurturing/masiva') ?>" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="accion" value="reanudar_todos">
                <button type="submit" class="btn btn--outline btn--xs"
                        onclick="return confirm('Reanudar todos los contactos pausados?')">Reanudar todos</button>
            </form>
            <form method="POST" action="<?= url('/admin/nurturing/masiva') ?>" style="display:inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="accion" value="reprogramar_todos">
                <button type="submit" class="btn btn--outline btn--xs"
                        onclick="return confirm('Reprogramar todos los contactos?')">Reprogramar todos</button>
            </form>
        </div>
    </div>

    <?php if (!empty($_SESSION['flash']['success'])): ?>
        <div class="alert alert--success"><?= e($_SESSION['flash']['success']) ?></div>
        <?php unset($_SESSION['flash']['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash']['error'])): ?>
        <div class="alert alert--danger"><?= e($_SESSION['flash']['error']) ?></div>
        <?php unset($_SESSION['flash']['error']); ?>
    <?php endif; ?>

    <!-- Tabs -->
    <div style="display:flex;gap:4px;margin-bottom:1rem;flex-wrap:wrap;">
        <?php
        $tabs = [
            'todos'       => ['Todos',       $contadores['todos']],
            'en_cola'     => ['En cola',     $contadores['en_cola']],
            'completados' => ['Completados', $contadores['completados']],
            'pausados'    => ['Pausados',    $contadores['pausados']],
            'desuscritos' => ['Desuscritos', $contadores['desuscritos']],
        ];
        foreach ($tabs as $val => [$label, $count]):
            $active = $filtro === $val;
        ?>
            <a href="<?= url('/admin/nurturing/contactos?filtro=' . $val) ?>"
               class="btn btn--sm <?= $active ? 'btn--primary' : 'btn--outline' ?>">
                <?= $label ?>
                <span style="background:<?= $active ? 'rgba(255,255,255,.3)' : 'var(--gray-200)' ?>;
                       padding:1px 6px;border-radius:10px;font-size:11px;margin-left:4px;">
                    <?= $count ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($contactos)): ?>
        <div class="empty-state">
            <div class="empty-state__icon">&#128236;</div>
            <div class="empty-state__title">Sin contactos</div>
            <div class="empty-state__text">No hay contactos en este filtro.</div>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Recordatorios</th>
                        <th>Ultimo envio</th>
                        <th>Proximo envio</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contactos as $c): ?>
                        <tr>
                            <td style="font-weight:600;"><?= e($c['nombre']) ?></td>
                            <td><a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a></td>
                            <td>
                                <span style="font-weight:600;"><?= $c['recordatorios_enviados'] ?>/<?= $maxRec ?></span>
                            </td>
                            <td>
                                <?= $c['ultimo_recordatorio_at']
                                    ? '<small>' . date('d/m/Y H:i', strtotime($c['ultimo_recordatorio_at'])) . '</small>'
                                    : '<span style="color:var(--gray-400);">--</span>' ?>
                            </td>
                            <td>
                                <?php if ($c['proximo_recordatorio_at']): ?>
                                    <small><?= date('d/m/Y H:i', strtotime($c['proximo_recordatorio_at'])) ?></small>
                                <?php else: ?>
                                    <span style="color:var(--gray-400);">--</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($c['desuscrito']): ?>
                                    <span class="badge badge--danger">Desuscrito</span>
                                <?php elseif ($c['nurturing_pausado']): ?>
                                    <span class="badge badge--warning">Pausado</span>
                                <?php elseif ($c['recordatorios_enviados'] >= $maxRec): ?>
                                    <span class="badge badge--success">Completado</span>
                                <?php elseif ($c['proximo_recordatorio_at']): ?>
                                    <span class="badge badge--info">En cola</span>
                                <?php else: ?>
                                    <span class="badge">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                    <?php if (!$c['desuscrito']): ?>
                                        <?php if ($c['nurturing_pausado']): ?>
                                            <form method="POST" action="<?= url('/admin/nurturing/contactos/' . $c['id'] . '/reanudar') ?>" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn--outline btn--xs">Reanudar</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="<?= url('/admin/nurturing/contactos/' . $c['id'] . '/pausar') ?>" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn--outline btn--xs">Pausar</button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($c['recordatorios_enviados'] < $maxRec): ?>
                                            <form method="POST" action="<?= url('/admin/nurturing/contactos/' . $c['id'] . '/enviar') ?>" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn--primary btn--xs"
                                                        onclick="return confirm('Enviar recordatorio ahora?')">Enviar ahora</button>
                                            </form>
                                            <form method="POST" action="<?= url('/admin/nurturing/contactos/' . $c['id'] . '/cancelar') ?>" style="display:inline;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn--danger btn--xs"
                                                        onclick="return confirm('Cancelar todos los recordatorios?')">Cancelar</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <a href="<?= url('/admin/mensajes/' . $c['id']) ?>" class="btn btn--outline btn--xs">Ver ficha</a>
                                </div>
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
