<?php
/**
 * Detalle de cambio pendiente — Revisar y aprobar/rechazar
 * Variables: $cambio, $comercio, $cambiosData, $labels
 */
?>

<div class="admin-breadcrumb">
    <a href="<?= url('/admin') ?>">Dashboard</a>
    <span>/</span>
    <a href="<?= url('/admin/cambios-pendientes') ?>">Cambios Pendientes</a>
    <span>/</span>
    <span>Revisar</span>
</div>

<h2 style="margin-bottom:0.5rem">Revisar cambios: <?= e($cambio['comercio_nombre']) ?></h2>
<p style="color:var(--color-gray);margin-bottom:1.25rem;font-size:0.9rem">
    Enviado por <strong><?= e($cambio['usuario_nombre']) ?></strong> (<?= e($cambio['usuario_email']) ?>)
    el <?= date('d/m/Y H:i', strtotime($cambio['created_at'])) ?>
</p>

<!-- Tabla comparativa de cambios -->
<div class="admin-card" style="margin-bottom:1.25rem">
    <div class="admin-card__header">
        <h3 class="admin-card__title">Cambios solicitados (<?= count($cambiosData) ?>)</h3>
    </div>
    <div class="admin-card__body" style="padding:0">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:20%">Campo</th>
                    <th style="width:40%">Valor actual</th>
                    <th style="width:40%">Nuevo valor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cambiosData as $campo => $valores): ?>
                    <tr>
                        <td><strong><?= e($labels[$campo] ?? $campo) ?></strong></td>
                        <td style="font-size:0.85rem">
                            <?php if ($campo === 'logo' || $campo === 'portada'): ?>
                                <?php if (!empty($valores['anterior'])): ?>
                                    <img src="<?= asset('img/' . ($campo === 'logo' ? 'logos' : 'portadas') . '/' . $valores['anterior']) ?>"
                                         alt="Actual" loading="lazy" style="max-height:60px;border-radius:4px">
                                <?php else: ?>
                                    <span style="color:var(--color-gray)">Sin imagen</span>
                                <?php endif; ?>
                            <?php elseif ($campo === 'categorias' || $campo === 'fechas'): ?>
                                <?php
                                $ids = $valores['anterior'] ?? [];
                                echo is_array($ids) ? implode(', ', $ids) : e((string)$ids);
                                ?>
                            <?php else: ?>
                                <?php
                                $ant = $valores['anterior'] ?? '';
                                echo $ant !== '' && $ant !== null ? e((string)$ant) : '<span style="color:var(--color-gray)">Vacío</span>';
                                ?>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:0.85rem;background:#F0FDF4">
                            <?php if ($campo === 'logo' || $campo === 'portada'): ?>
                                <?php if (!empty($valores['nuevo'])): ?>
                                    <img src="<?= asset('img/' . ($campo === 'logo' ? 'logos' : 'portadas') . '/' . $valores['nuevo']) ?>"
                                         alt="Nuevo" loading="lazy" style="max-height:60px;border-radius:4px">
                                <?php else: ?>
                                    <span style="color:var(--color-gray)">Sin imagen</span>
                                <?php endif; ?>
                            <?php elseif ($campo === 'categorias' || $campo === 'fechas'): ?>
                                <?php
                                $ids = $valores['nuevo'] ?? [];
                                echo is_array($ids) ? implode(', ', $ids) : e((string)$ids);
                                ?>
                            <?php else: ?>
                                <?php
                                $nue = $valores['nuevo'] ?? '';
                                echo $nue !== '' && $nue !== null ? '<strong>' . e((string)$nue) . '</strong>' : '<span style="color:var(--color-gray)">Vacío</span>';
                                ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($cambio['estado'] === 'pendiente'): ?>
    <!-- Acciones -->
    <div class="admin-card" style="margin-bottom:1.25rem">
        <div class="admin-card__header">
            <h3 class="admin-card__title">Acción</h3>
        </div>
        <div class="admin-card__body">
            <div class="form-group" style="margin-bottom:1rem">
                <label class="form-label">Notas (opcional)</label>
                <textarea id="notasCambio" class="form-control" rows="2"
                          placeholder="Notas internas sobre esta revisión..."></textarea>
            </div>

            <div style="display:flex;gap:0.75rem">
                <form method="POST" action="<?= url('/admin/cambios-pendientes/aprobar/' . $cambio['id']) ?>" style="flex:1">
                    <?= csrf_field() ?>
                    <input type="hidden" name="notas" id="notasAprobar" value="">
                    <button type="submit" class="btn btn--primary" style="width:100%"
                            onclick="document.getElementById('notasAprobar').value=document.getElementById('notasCambio').value">
                        ✅ Aprobar y aplicar cambios
                    </button>
                </form>

                <form method="POST" action="<?= url('/admin/cambios-pendientes/rechazar/' . $cambio['id']) ?>" style="flex:1">
                    <?= csrf_field() ?>
                    <input type="hidden" name="notas" id="notasRechazar" value="">
                    <button type="submit" class="btn btn--danger" style="width:100%"
                            onclick="document.getElementById('notasRechazar').value=document.getElementById('notasCambio').value">
                        ❌ Rechazar cambios
                    </button>
                </form>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="admin-card">
        <div class="admin-card__body">
            <p>
                <strong>Estado:</strong>
                <?= $cambio['estado'] === 'aprobado' ? '✅ Aprobado' : '❌ Rechazado' ?>
                el <?= date('d/m/Y H:i', strtotime($cambio['revisado_at'])) ?>
            </p>
            <?php if (!empty($cambio['notas'])): ?>
                <p><strong>Notas:</strong> <?= e($cambio['notas']) ?></p>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div style="margin-top:1rem">
    <a href="<?= url('/admin/cambios-pendientes') ?>" class="btn btn--outline">← Volver al listado</a>
    <a href="<?= url('/admin/comercios/editar/' . $cambio['comercio_id']) ?>" class="btn btn--outline">Ver comercio en admin</a>
</div>
