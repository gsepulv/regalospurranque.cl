<?php
/**
 * Admin - Gestión de Planes y Validación
 * Variables: $tab, $planes, $conteos, $comercios, $validados, $noValidados
 */
$totalComercios = array_sum($conteos);
?>
<style>
.plans-overview{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:.75rem;margin-bottom:1.5rem}
.plan-card{text-align:center;padding:1rem;border-radius:10px;background:#fff;border:1px solid #e2e8f0;transition:transform .2s,box-shadow .2s}
.plan-card:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.08)}
.plan-card__icon{font-size:1.3rem}
.plan-card__name{font-weight:700;font-size:.9rem;margin:.25rem 0}
.plan-card__price{font-size:.7rem;color:#718096}
.plan-card__count{font-size:1.6rem;font-weight:800;margin-top:.35rem}
.plan-card__label{font-size:.65rem;color:#a0aec0;text-transform:uppercase;letter-spacing:.05em}
.tabs{display:flex;gap:0;border-bottom:2px solid #e2e8f0;margin-bottom:1.5rem}
.tab-link{padding:.65rem 1.1rem;font-weight:600;font-size:.875rem;color:#718096;text-decoration:none;border-bottom:2px solid transparent;margin-bottom:-2px;transition:all .2s}
.tab-link:hover{color:#4a5568}
.tab-link--active{color:var(--color-primary,#E8833A);border-bottom-color:var(--color-primary,#E8833A)}
.plan-row{display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr auto;gap:.5rem;align-items:center;padding:.65rem .75rem;border-bottom:1px solid #f0f0f0;font-size:.85rem}
.plan-row:hover{background:#f7fafc}
.plan-row--header{background:#f7fafc;font-weight:700;font-size:.75rem;color:#4a5568;border-bottom:2px solid #e2e8f0}
.sello-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.5rem}
.sello-item{display:flex;align-items:center;justify-content:space-between;padding:.6rem .8rem;border-radius:8px;border:1px solid #e2e8f0}
.sello-item--on{background:#f0fff4;border-color:#c6f6d5}
.sello-item--off{background:#fff5f5;border-color:#fed7d7}
.features-icons{font-size:.75rem;letter-spacing:.1em}
@media(max-width:768px){
    .plans-overview{grid-template-columns:repeat(2,1fr)}
    .plan-row{grid-template-columns:1fr;gap:.2rem;padding:.75rem}
    .plan-row--header{display:none}
    .sello-grid{grid-template-columns:1fr}
}
</style>

<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <span>Planes y Validación</span>
</div>

<h2>Planes y Validación</h2>

<!-- ═══ TABS ═══ -->
<div class="tabs">
    <a href="<?= url('/admin/planes?tab=config') ?>" class="tab-link <?= $tab === 'config' ? 'tab-link--active' : '' ?>">📋 Configuración</a>
    <a href="<?= url('/admin/planes?tab=asignar') ?>" class="tab-link <?= $tab === 'asignar' ? 'tab-link--active' : '' ?>">🏪 Asignar a Comercios</a>
    <a href="<?= url('/admin/planes?tab=validacion') ?>" class="tab-link <?= $tab === 'validacion' ? 'tab-link--active' : '' ?>">🛡️ Validación</a>
</div>

<?php if ($tab === 'config'): ?>
<!-- ══════════════════════════════════════ -->
<!-- TAB 1: CONFIGURACIÓN DE PLANES       -->
<!-- ══════════════════════════════════════ -->

<!-- Resumen visual -->
<div class="plans-overview">
    <?php foreach ($planes as $p): ?>
    <div class="plan-card" style="border-top:3px solid <?= e($p['color']) ?>">
        <div class="plan-card__icon"><?= $p['icono'] ?></div>
        <div class="plan-card__name"><?= e($p['nombre']) ?></div>
        <div class="plan-card__price">
            <?= $p['precio_regular'] > 0 ? '$' . number_format($p['precio_regular'], 0, ',', '.') . '/mes' : 'Gratis' ?>
        </div>
        <div class="plan-card__count" style="color:<?= e($p['color']) ?>"><?= $conteos[$p['slug']] ?? 0 ?></div>
        <div class="plan-card__label">comercios</div>
    </div>
    <?php endforeach; ?>
</div>

<div class="toolbar" style="margin-bottom:1rem">
    <h3>Planes configurados</h3>
    <a href="<?= url('/admin/planes/crear') ?>" class="btn btn--primary btn--sm">+ Nuevo Plan</a>
</div>

<div class="admin-card">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>Plan</th>
                    <th>Precio intro</th>
                    <th>Precio regular</th>
                    <th>Fotos</th>
                    <th>Posición</th>
                    <th>Cupos</th>
                    <th>Características</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($planes as $p): ?>
                <tr>
                    <td style="text-align:center;color:#a0aec0"><?= $p['orden'] ?></td>
                    <td>
                        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:<?= e($p['color']) ?>;vertical-align:middle;margin-right:.25rem"></span>
                        <?= $p['icono'] ?> <strong><?= e($p['nombre']) ?></strong>
                        <br><code style="font-size:.7rem;color:#a0aec0"><?= e($p['slug']) ?></code>
                    </td>
                    <td>$<?= number_format($p['precio_intro'], 0, ',', '.') ?></td>
                    <td><strong>$<?= number_format($p['precio_regular'], 0, ',', '.') ?></strong></td>
                    <td style="text-align:center"><?= $p['max_fotos'] ?></td>
                    <td>
                        <?php if ($p['posicion'] === 'primero'): ?>
                            <span style="color:#D69E2E;font-weight:700">SIEMPRE 1º</span>
                        <?php elseif ($p['posicion'] === 'prioritaria'): ?>
                            <span style="color:#805AD5">Prioritaria</span>
                        <?php else: ?>
                            Normal
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center">
                        <?php if ($p['max_cupos_categoria']): ?>
                            <?= $p['max_cupos_categoria'] ?>/cat.
                        <?php elseif ($p['max_cupos']): ?>
                            <?= $p['max_cupos'] ?>
                        <?php else: ?>
                            ∞
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="features-icons">
                            <?= $p['tiene_mapa'] ? '🗺️' : '' ?>
                            <?= $p['tiene_horarios'] ? '🕐' : '' ?>
                            <?= $p['tiene_sello'] ? '✅' : '' ?>
                            <?= $p['tiene_reporte'] ? '📊' : '' ?>
                            <?= $p['max_redes'] > 1 ? '📱' : '' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($p['activo']): ?>
                            <span class="status status--active">Activo</span>
                        <?php else: ?>
                            <span class="status status--inactive">Inactivo</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:.25rem">
                            <a href="<?= url('/admin/planes/editar/' . $p['id']) ?>" class="btn btn--outline btn--sm" title="Editar">✏️</a>
                            <?php if (($conteos[$p['slug']] ?? 0) === 0): ?>
                            <form method="POST" action="<?= url('/admin/planes/eliminar/' . $p['id']) ?>"
                                  onsubmit="return confirm('¿Eliminar el plan <?= e($p['nombre']) ?>?')">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn--danger btn--sm" title="Eliminar">🗑️</button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php elseif ($tab === 'asignar'): ?>
<!-- ══════════════════════════════════════ -->
<!-- TAB 2: ASIGNAR PLANES A COMERCIOS    -->
<!-- ══════════════════════════════════════ -->

<div class="admin-card">
    <div class="admin-card__header">
        <h3>🏪 Comercios y sus planes (<?= count($comercios) ?>)</h3>
    </div>

    <!-- Encabezado -->
    <div class="plan-row plan-row--header">
        <div>Comercio</div>
        <div>Plan</div>
        <div>Precio CLP</div>
        <div>Inicio</div>
        <div>Fin</div>
        <div></div>
    </div>

    <?php foreach ($comercios as $c): ?>
    <form method="POST" action="<?= url('/admin/planes/assign') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="comercio_id" value="<?= $c['id'] ?>">
        <div class="plan-row">
            <div>
                <strong><?= e($c['nombre']) ?></strong>
                <?php if (!empty($c['validado'])): ?>
                    <span class="badge badge--success" style="font-size:.6rem;padding:.1rem .3rem">🛡️ Validado</span>
                <?php endif; ?>
                <?php if (!$c['activo']): ?>
                    <span class="status status--inactive" style="font-size:.65rem">Inactivo</span>
                <?php endif; ?>
                <br><small style="color:#a0aec0"><?= e($c['categorias'] ?? 'Sin categoría') ?></small>
            </div>
            <div>
                <select name="plan" class="form-control" style="padding:.3rem .4rem;font-size:.8rem;width:130px">
                    <?php foreach ($planes as $p): ?>
                    <option value="<?= e($p['slug']) ?>" <?= $c['plan'] === $p['slug'] ? 'selected' : '' ?>>
                        <?= $p['icono'] ?> <?= e($p['nombre']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <input type="number" name="plan_precio" class="form-control"
                       value="<?= e($c['plan_precio'] ?? '') ?>"
                       placeholder="0" style="padding:.3rem;font-size:.8rem;width:80px">
            </div>
            <div>
                <input type="date" name="plan_inicio" class="form-control"
                       value="<?= e($c['plan_inicio'] ?? '') ?>"
                       style="padding:.25rem;font-size:.75rem">
            </div>
            <div>
                <input type="date" name="plan_fin" class="form-control"
                       value="<?= e($c['plan_fin'] ?? '') ?>"
                       style="padding:.25rem;font-size:.75rem">
            </div>
            <div>
                <button type="submit" class="btn btn--primary btn--sm">💾</button>
            </div>
        </div>
    </form>
    <?php endforeach; ?>
</div>

<?php elseif ($tab === 'validacion'): ?>
<!-- ══════════════════════════════════════ -->
<!-- TAB 3: VALIDACIÓN DE COMERCIOS       -->
<!-- ══════════════════════════════════════ -->

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:1fr 1fr;margin-bottom:1.5rem">
    <div class="stat-card stat-card--success">
        <div class="stat-card__header">
            <div>
                <div class="stat-card__number"><?= count($validados) ?></div>
                <div class="stat-card__label">Comercios Validados</div>
            </div>
            <div class="stat-card__icon">🛡️</div>
        </div>
    </div>
    <div class="stat-card stat-card--danger">
        <div class="stat-card__header">
            <div>
                <div class="stat-card__number"><?= count($noValidados) ?></div>
                <div class="stat-card__label">Pendientes</div>
            </div>
            <div class="stat-card__icon">⏳</div>
        </div>
    </div>
</div>

<!-- Control de Sello Verificado por Plan -->
<div class="admin-card" style="margin-bottom:1.5rem">
    <div class="admin-card__header"><h3>✅ Sello Verificado por Plan</h3></div>
    <div class="admin-card__body">
        <p style="font-size:.8rem;color:#718096;margin-bottom:.75rem">
            Controla qué planes muestran el sello "Verificado" automáticamente. Esto es independiente de la validación manual (🛡️).
        </p>
        <div class="sello-grid">
            <?php foreach ($planes as $p): ?>
            <form method="POST" action="<?= url('/admin/planes/toggle-sello') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="plan_id" value="<?= $p['id'] ?>">
                <div class="sello-item <?= $p['tiene_sello'] ? 'sello-item--on' : 'sello-item--off' ?>">
                    <span><?= $p['icono'] ?> <strong><?= e($p['nombre']) ?></strong></span>
                    <button type="submit" class="btn btn--sm" style="font-size:.75rem;padding:.2rem .5rem;
                        background:<?= $p['tiene_sello'] ? '#38A169' : '#E53E3E' ?>;color:#fff;border:none">
                        <?= $p['tiene_sello'] ? '✅ Activo' : '❌ Inactivo' ?>
                    </button>
                </div>
            </form>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Pendientes -->
<div class="admin-card" style="margin-bottom:1.5rem">
    <div class="admin-card__header"><h3>⏳ Pendientes de validación</h3></div>
    <?php if (empty($noValidados)): ?>
        <div class="admin-card__body" style="text-align:center;color:#a0aec0;padding:2rem">
            Todos los comercios están validados 🎉
        </div>
    <?php else: ?>
        <?php foreach ($noValidados as $c): ?>
        <div style="padding:.75rem 1rem;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem">
            <div>
                <strong><?= e($c['nombre']) ?></strong>
                <span class="badge badge--<?= e($c['plan']) ?>" style="font-size:.65rem"><?= ucfirst(e($c['plan'])) ?></span>
                <br><small style="color:#a0aec0"><?= e($c['direccion'] ?? 'Sin dirección') ?></small>
            </div>
            <form method="POST" action="<?= url('/admin/planes/validar') ?>" style="display:flex;gap:.35rem;align-items:center">
                <?= csrf_field() ?>
                <input type="hidden" name="comercio_id" value="<?= $c['id'] ?>">
                <input type="hidden" name="validar" value="1">
                <input type="text" name="validado_notas" class="form-control"
                       placeholder="Notas de validación..." style="font-size:.8rem;padding:.3rem;width:180px">
                <button type="submit" class="btn btn--primary btn--sm">🛡️ Validar</button>
            </form>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Ya validados -->
<div class="admin-card">
    <div class="admin-card__header"><h3>🛡️ Comercios Validados</h3></div>
    <?php if (empty($validados)): ?>
        <div class="admin-card__body" style="text-align:center;color:#a0aec0;padding:2rem">
            Aún no hay comercios validados.
        </div>
    <?php else: ?>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead><tr><th>Comercio</th><th>Plan</th><th>Fecha validación</th><th>Notas</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php foreach ($validados as $c): ?>
                <tr>
                    <td><strong><?= e($c['nombre']) ?></strong></td>
                    <td><span class="badge badge--<?= e($c['plan']) ?>"><?= ucfirst(e($c['plan'])) ?></span></td>
                    <td style="font-size:.85rem"><?= $c['validado_fecha'] ? date('d/m/Y', strtotime($c['validado_fecha'])) : '—' ?></td>
                    <td style="font-size:.85rem;color:#718096"><?= e($c['validado_notas'] ?? '') ?></td>
                    <td>
                        <form method="POST" action="<?= url('/admin/planes/validar') ?>"
                              onsubmit="return confirm('¿Quitar validación de <?= e($c['nombre']) ?>?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="comercio_id" value="<?= $c['id'] ?>">
                            <button type="submit" class="btn btn--danger btn--sm">Quitar ✗</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php endif; ?>
