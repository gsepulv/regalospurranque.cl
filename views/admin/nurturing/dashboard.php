<div class="admin-page">
    <div class="admin-page__header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <h1>Recordatorios (Nurturing)</h1>
        <div style="display:flex;gap:8px;align-items:center;">
            <a href="<?= url('/admin/nurturing/configuracion') ?>" class="btn btn--outline btn--sm">Configuracion</a>
            <a href="<?= url('/admin/nurturing/plantillas') ?>" class="btn btn--outline btn--sm">Plantillas</a>
            <a href="<?= url('/admin/nurturing/contactos') ?>" class="btn btn--outline btn--sm">Contactos</a>
        </div>
    </div>

    <?php if (!empty($_SESSION['flash']['success'])): ?>
        <div class="alert alert--success"><?= e($_SESSION['flash']['success']) ?></div>
        <?php unset($_SESSION['flash']['success']); ?>
    <?php endif; ?>

    <!-- Toggle servicio -->
    <div class="card" style="margin-bottom:1.5rem;border-left:4px solid <?= $activo ? 'var(--success)' : 'var(--danger)' ?>;">
        <div class="card__body" style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h3 style="margin:0 0 4px;">
                    Servicio de recordatorios:
                    <span style="color:<?= $activo ? 'var(--success)' : 'var(--danger)' ?>;">
                        <?= $activo ? 'ACTIVO' : 'INACTIVO' ?>
                    </span>
                </h3>
                <small style="color:var(--gray-500);">
                    <?= $activo ? 'Los recordatorios se enviaran segun la programacion del cron.' : 'Ningun recordatorio se enviara hasta que se active.' ?>
                </small>
            </div>
            <form method="POST" action="<?= url('/admin/nurturing/toggle') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn <?= $activo ? 'btn--danger' : 'btn--success' ?>"
                        onclick="return confirm('<?= $activo ? 'Desactivar' : 'Activar' ?> el servicio de recordatorios?')">
                    <?= $activo ? 'Desactivar' : 'Activar' ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Cards de metricas -->
    <div class="stats-grid" style="margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-card__value"><?= $enviadosHoy ?></div>
            <div class="stat-card__label">Enviados hoy</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value"><?= $enviadosSem ?></div>
            <div class="stat-card__label">Esta semana</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value"><?= $enviadosMes ?></div>
            <div class="stat-card__label">Este mes</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value" style="color:var(--primary)"><?= $enCola ?></div>
            <div class="stat-card__label">En cola</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value" style="color:var(--danger)"><?= $desuscritos ?></div>
            <div class="stat-card__label">Desuscritos</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value"><?= $stats['tasa_exito'] ?>%</div>
            <div class="stat-card__label">Tasa de exito</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
        <!-- Proximos envios -->
        <div class="card">
            <div class="card__header" style="display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;">Proximos envios programados</h3>
                <a href="<?= url('/admin/nurturing/contactos?filtro=en_cola') ?>" style="font-size:12px;">Ver todos</a>
            </div>
            <div class="card__body" style="padding:0;">
                <?php if (empty($proximos)): ?>
                    <p style="padding:16px;color:var(--gray-500);margin:0;">Sin envios programados.</p>
                <?php else: ?>
                    <table class="table" style="font-size:13px;margin:0;">
                        <thead>
                            <tr>
                                <th>Contacto</th>
                                <th>R#</th>
                                <th>Programado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($proximos, 0, 10) as $p): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight:600;"><?= e($p['nombre']) ?></div>
                                        <small style="color:var(--gray-500);"><?= e($p['email']) ?></small>
                                    </td>
                                    <td><?= $p['recordatorios_enviados'] + 1 ?>/<?= $maxRec ?></td>
                                    <td><small><?= date('d/m H:i', strtotime($p['proximo_recordatorio_at'])) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Ultimos envios -->
        <div class="card">
            <div class="card__header">
                <h3 style="margin:0;">Ultimos envios</h3>
            </div>
            <div class="card__body" style="padding:0;">
                <?php if (empty($ultimos)): ?>
                    <p style="padding:16px;color:var(--gray-500);margin:0;">Sin envios registrados.</p>
                <?php else: ?>
                    <table class="table" style="font-size:13px;margin:0;">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Contacto</th>
                                <th>R#</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($ultimos, 0, 10) as $u): ?>
                                <tr>
                                    <td><small><?= date('d/m H:i', strtotime($u['created_at'])) ?></small></td>
                                    <td><?= e($u['nombre'] ?? $u['email_destino']) ?></td>
                                    <td><?= $u['numero_recordatorio'] ?></td>
                                    <td>
                                        <?php if ($u['estado_envio'] === 'enviado'): ?>
                                            <span class="badge badge--success">Enviado</span>
                                        <?php elseif ($u['estado_envio'] === 'fallido'): ?>
                                            <span class="badge badge--danger">Fallido</span>
                                        <?php else: ?>
                                            <span class="badge badge--secondary">Cancelado</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Grafico envios por dia -->
    <?php if (!empty($stats['por_dia'])): ?>
    <div class="card">
        <div class="card__header">
            <h3 style="margin:0;">Envios por dia (ultimos 30 dias)</h3>
        </div>
        <div class="card__body">
            <?php
            $maxDia = max(array_column($stats['por_dia'], 'total'));
            $maxDia = $maxDia ?: 1;
            ?>
            <div style="display:flex;align-items:end;gap:2px;height:100px;">
                <?php foreach ($stats['por_dia'] as $dia): ?>
                    <?php $h = round(($dia['total'] / $maxDia) * 100); ?>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;">
                        <small style="font-size:9px;color:var(--gray-500);"><?= $dia['total'] ?></small>
                        <div style="width:100%;background:var(--primary);border-radius:2px 2px 0 0;
                                    height:<?= max($h, 2) ?>px;min-width:4px;"
                             title="<?= $dia['fecha'] ?>: <?= $dia['total'] ?>"></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:4px;">
                <small style="color:var(--gray-500);"><?= $stats['por_dia'][0]['fecha'] ?? '' ?></small>
                <small style="color:var(--gray-500);"><?= end($stats['por_dia'])['fecha'] ?? '' ?></small>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
