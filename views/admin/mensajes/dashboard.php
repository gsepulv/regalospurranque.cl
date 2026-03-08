<div class="admin-page">
    <div class="admin-page__header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <h1>Dashboard de Conversiones</h1>
        <a href="<?= url('/admin/mensajes') ?>" class="btn btn--outline btn--sm">
            &#8592; Volver a mensajes
        </a>
    </div>

    <!-- Filtro por fechas -->
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__body" style="padding:12px;">
            <form method="GET" action="<?= url('/admin/mensajes/dashboard') ?>" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;">
                <div class="form-group" style="margin:0;">
                    <label style="font-size:12px;">Desde</label>
                    <input type="date" name="desde" class="form-control" value="<?= e($filters['desde'] ?? '') ?>">
                </div>
                <div class="form-group" style="margin:0;">
                    <label style="font-size:12px;">Hasta</label>
                    <input type="date" name="hasta" class="form-control" value="<?= e($filters['hasta'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn--primary btn--sm">Filtrar</button>
                <a href="<?= url('/admin/mensajes/dashboard') ?>" class="btn btn--outline btn--sm">Limpiar</a>
            </form>
        </div>
    </div>

    <!-- Cards de metricas -->
    <div class="stats-grid" style="margin-bottom:1.5rem;">
        <div class="stat-card">
            <div class="stat-card__value"><?= number_format($stats['total']) ?></div>
            <div class="stat-card__label">Total mensajes</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value" style="color:var(--success)">
                <?= $stats['tasa_conversion'] ?>%
            </div>
            <div class="stat-card__label">Tasa de conversion</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value"><?= $nurturingStats['enviados_semana'] ?? 0 ?></div>
            <div class="stat-card__label">Recordatorios esta semana</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value" style="color:var(--danger)">
                <?= $nurturingStats['tasa_desuscripcion'] ?? '0' ?>%
            </div>
            <div class="stat-card__label">Tasa desuscripcion</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value">
                <?php if ($stats['tiempo_respuesta'] !== null): ?>
                    <?php if ($stats['tiempo_respuesta'] >= 1440): ?>
                        <?= round($stats['tiempo_respuesta'] / 1440, 1) ?>d
                    <?php elseif ($stats['tiempo_respuesta'] >= 60): ?>
                        <?= round($stats['tiempo_respuesta'] / 60, 1) ?>h
                    <?php else: ?>
                        <?= $stats['tiempo_respuesta'] ?>m
                    <?php endif; ?>
                <?php else: ?>
                    --
                <?php endif; ?>
            </div>
            <div class="stat-card__label">Tiempo promedio respuesta</div>
        </div>
        <div class="stat-card">
            <div class="stat-card__value">
                <?php if ($stats['tiempo_conversion'] !== null): ?>
                    <?php if ($stats['tiempo_conversion'] >= 1440): ?>
                        <?= round($stats['tiempo_conversion'] / 1440, 1) ?>d
                    <?php elseif ($stats['tiempo_conversion'] >= 60): ?>
                        <?= round($stats['tiempo_conversion'] / 60, 1) ?>h
                    <?php else: ?>
                        <?= $stats['tiempo_conversion'] ?>m
                    <?php endif; ?>
                <?php else: ?>
                    --
                <?php endif; ?>
            </div>
            <div class="stat-card__label">Tiempo promedio conversion</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
        <!-- Funnel visual -->
        <div class="card">
            <div class="card__header">
                <h3 style="margin:0;">Funnel de conversion</h3>
            </div>
            <div class="card__body">
                <?php
                $porEstado = $stats['por_estado'];
                $total = $stats['total'] ?: 1;
                $funnel = [
                    ['Recibidos',      $stats['total'],                  'var(--gray-400)'],
                    ['Leidos',         ($porEstado['leido'] ?? 0) + ($porEstado['respondido'] ?? 0) + ($porEstado['convertido'] ?? 0), 'var(--info)'],
                    ['Respondidos',    ($porEstado['respondido'] ?? 0) + ($porEstado['convertido'] ?? 0), 'var(--primary)'],
                    ['Recordatorios',  $nurturingStats['enviados_semana'] ?? 0, '#8b5cf6'],
                    ['Convertidos',    $porEstado['convertido'] ?? 0,   'var(--success)'],
                ];
                foreach ($funnel as [$fLabel, $fCount, $fColor]):
                    $pct = round(($fCount / $total) * 100);
                ?>
                    <div style="margin-bottom:12px;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px;font-size:13px;">
                            <span><?= $fLabel ?></span>
                            <span style="font-weight:600;"><?= $fCount ?> (<?= $pct ?>%)</span>
                        </div>
                        <div style="background:var(--gray-100);border-radius:4px;height:24px;overflow:hidden;">
                            <div style="background:<?= $fColor ?>;height:100%;width:<?= $pct ?>%;border-radius:4px;
                                        transition:width .3s;min-width:<?= $fCount > 0 ? '2px' : '0' ?>;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top asuntos -->
        <div class="card">
            <div class="card__header">
                <h3 style="margin:0;">Top 5 asuntos</h3>
            </div>
            <div class="card__body">
                <?php if (empty($stats['top_asuntos'])): ?>
                    <p style="color:var(--gray-500);margin:0;">Sin datos.</p>
                <?php else: ?>
                    <table class="table" style="font-size:13px;">
                        <thead>
                            <tr>
                                <th>Asunto</th>
                                <th style="text-align:right;">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stats['top_asuntos'] as $a): ?>
                                <tr>
                                    <td><?= e(mb_substr($a['asunto'], 0, 60)) ?></td>
                                    <td style="text-align:right;font-weight:600;"><?= $a['total'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Mensajes por dia (ultimos 30 dias) -->
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__header">
            <h3 style="margin:0;">Mensajes por dia (ultimos 30 dias)</h3>
        </div>
        <div class="card__body">
            <?php if (empty($stats['por_dia'])): ?>
                <p style="color:var(--gray-500);margin:0;">Sin datos.</p>
            <?php else: ?>
                <?php
                $maxDia = max(array_column($stats['por_dia'], 'total'));
                $maxDia = $maxDia ?: 1;
                ?>
                <div style="display:flex;align-items:end;gap:2px;height:120px;">
                    <?php foreach ($stats['por_dia'] as $dia): ?>
                        <?php $h = round(($dia['total'] / $maxDia) * 100); ?>
                        <div style="flex:1;display:flex;flex-direction:column;align-items:center;">
                            <small style="font-size:9px;color:var(--gray-500);"><?= $dia['total'] ?></small>
                            <div style="width:100%;background:var(--primary);border-radius:2px 2px 0 0;
                                        height:<?= max($h, 2) ?>px;min-width:4px;"
                                 title="<?= $dia['fecha'] ?>: <?= $dia['total'] ?> mensajes"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:4px;">
                    <small style="color:var(--gray-500);"><?= $stats['por_dia'][0]['fecha'] ?? '' ?></small>
                    <small style="color:var(--gray-500);"><?= end($stats['por_dia'])['fecha'] ?? '' ?></small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Conversiones recientes -->
    <div class="card">
        <div class="card__header">
            <h3 style="margin:0;">Conversiones recientes</h3>
        </div>
        <div class="card__body">
            <?php if (empty($recientes)): ?>
                <p style="color:var(--gray-500);margin:0;">Sin conversiones registradas.</p>
            <?php else: ?>
                <table class="table" style="font-size:13px;">
                    <thead>
                        <tr>
                            <th>Fecha contacto</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Comercio</th>
                            <th>Fecha conversion</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recientes as $r): ?>
                            <tr>
                                <td><small><?= date('d/m/Y', strtotime($r['created_at'])) ?></small></td>
                                <td><?= e($r['nombre']) ?></td>
                                <td><?= e($r['email']) ?></td>
                                <td>
                                    <?php if (!empty($r['comercio_nombre'])): ?>
                                        <?= e($r['comercio_nombre']) ?>
                                    <?php else: ?>
                                        <span style="color:var(--gray-400);">--</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= $r['convertido_at'] ? date('d/m/Y', strtotime($r['convertido_at'])) : '--' ?>
                                </td>
                                <td>
                                    <a href="<?= url('/admin/mensajes/' . $r['id']) ?>" class="btn btn--outline btn--xs">Ver</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
