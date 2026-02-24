<div class="admin-page">
    <div class="admin-page__header">
        <h1>Mensajes de Contacto</h1>
    </div>

    <!-- Estadísticas -->
    <div class="stats-grid" style="margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-card__value"><?= number_format($stats['total']) ?></div>
            <div class="stat-card__label">Total mensajes</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value" style="color:var(--warning)"><?= number_format($stats['no_leidos']) ?></div>
            <div class="stat-card__label">No leídos</div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__body">
            <form class="filters-row" method="GET" action="<?= url('/admin/contacto') ?>">
                <div class="form-group">
                    <select name="estado" class="form-control" onchange="this.form.submit()">
                        <option value="">Todos</option>
                        <option value="no_leido" <?= ($filters['estado'] ?? '') === 'no_leido' ? 'selected' : '' ?>>No leídos</option>
                        <option value="leido" <?= ($filters['estado'] ?? '') === 'leido' ? 'selected' : '' ?>>Leídos</option>
                        <option value="respondido" <?= ($filters['estado'] ?? '') === 'respondido' ? 'selected' : '' ?>>Respondidos</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <?php if (empty($mensajes)): ?>
        <div class="empty-state">
            <p>No hay mensajes de contacto.</p>
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
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mensajes as $msg): ?>
                        <tr style="<?= !$msg['leido'] ? 'font-weight:600;' : '' ?>">
                            <td>
                                <small><?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?></small>
                            </td>
                            <td><?= e($msg['nombre']) ?></td>
                            <td><a href="mailto:<?= e($msg['email']) ?>"><?= e($msg['email']) ?></a></td>
                            <td><?= e(mb_substr($msg['asunto'], 0, 50)) ?></td>
                            <td>
                                <?php if ($msg['respondido']): ?>
                                    <span class="badge badge--success">Respondido</span>
                                <?php elseif ($msg['leido']): ?>
                                    <span class="badge">Leído</span>
                                <?php else: ?>
                                    <span class="badge badge--warning">Nuevo</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn--small btn--outline" onclick="toggleMensaje(<?= $msg['id'] ?>)">
                                    Ver
                                </button>
                                <a href="<?= url('/admin/correos/enviar?mensaje_id=' . $msg['id']) ?>" class="btn btn--small btn--primary">
                                    Responder
                                </a>
                            </td>
                        </tr>
                        <tr id="msg-<?= $msg['id'] ?>" style="display:none;">
                            <td colspan="6">
                                <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:16px;margin:4px 0;">
                                    <p style="margin:0 0 8px;color:#64748b;font-size:13px;">
                                        <strong><?= e($msg['nombre']) ?></strong> &lt;<?= e($msg['email']) ?>&gt; — <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                                    </p>
                                    <p style="margin:0;white-space:pre-wrap;color:#334155;"><?= e($msg['mensaje']) ?></p>
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

<script>
function toggleMensaje(id) {
    var row = document.getElementById('msg-' + id);
    if (row) {
        row.style.display = row.style.display === 'none' ? '' : 'none';
    }
}
</script>
