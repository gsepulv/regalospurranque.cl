<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="<?= url('/admin/mensajes') ?>">Mensajes</a>
        <span>/</span>
        <span>Mensaje #<?= $mensaje['id'] ?></span>
    </div>

    <?php if (!empty($_SESSION['flash']['success'])): ?>
        <div class="alert alert--success"><?= e($_SESSION['flash']['success']) ?></div>
        <?php unset($_SESSION['flash']['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash']['error'])): ?>
        <div class="alert alert--danger"><?= e($_SESSION['flash']['error']) ?></div>
        <?php unset($_SESSION['flash']['error']); ?>
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:1fr 300px;gap:1.5rem;align-items:start;">
        <!-- Columna principal -->
        <div>
            <!-- Mensaje original -->
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card__header">
                    <h3 style="margin:0;">Mensaje original</h3>
                </div>
                <div class="card__body">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                        <div>
                            <small style="color:var(--gray-500);">Nombre</small>
                            <div style="font-weight:600;"><?= e($mensaje['nombre']) ?></div>
                        </div>
                        <div>
                            <small style="color:var(--gray-500);">Email</small>
                            <div><a href="mailto:<?= e($mensaje['email']) ?>"><?= e($mensaje['email']) ?></a></div>
                        </div>
                        <div>
                            <small style="color:var(--gray-500);">Asunto</small>
                            <div><?= e($mensaje['asunto']) ?></div>
                        </div>
                        <div>
                            <small style="color:var(--gray-500);">Fecha</small>
                            <div><?= date('d/m/Y H:i', strtotime($mensaje['created_at'])) ?></div>
                        </div>
                        <?php if (!empty($mensaje['ip'])): ?>
                        <div>
                            <small style="color:var(--gray-500);">IP</small>
                            <div style="font-family:monospace;font-size:13px;"><?= e($mensaje['ip']) ?></div>
                        </div>
                        <?php endif; ?>
                        <div>
                            <small style="color:var(--gray-500);">Fuente</small>
                            <div><?= e($mensaje['fuente'] ?? 'formulario') ?></div>
                        </div>
                    </div>
                    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:16px;">
                        <p style="margin:0;white-space:pre-wrap;color:#334155;"><?= e($mensaje['mensaje']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Comercio vinculado -->
            <?php if (!empty($mensaje['comercio_id'])): ?>
            <div class="card" style="margin-bottom:1.5rem;border-left:4px solid var(--success);">
                <div class="card__body" style="display:flex;justify-content:space-between;align-items:center;">
                    <div>
                        <span class="badge badge--success">&#10003; Convertido</span>
                        <strong style="margin-left:8px;"><?= e($mensaje['comercio_nombre'] ?? 'Comercio #' . $mensaje['comercio_id']) ?></strong>
                        <?php if (!empty($mensaje['convertido_at'])): ?>
                            <small style="color:var(--gray-500);margin-left:8px;">
                                el <?= date('d/m/Y H:i', strtotime($mensaje['convertido_at'])) ?>
                            </small>
                        <?php endif; ?>
                    </div>
                    <a href="<?= url('/admin/comercios/editar/' . $mensaje['comercio_id']) ?>"
                       class="btn btn--outline btn--xs">Ver comercio</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Timeline de respuestas -->
            <div class="card" style="margin-bottom:1.5rem;">
                <div class="card__header">
                    <h3 style="margin:0;">Historial de respuestas (<?= count($respuestas) ?>)</h3>
                </div>
                <div class="card__body">
                    <?php if (empty($respuestas)): ?>
                        <p style="color:var(--gray-500);margin:0;">Sin respuestas enviadas aun.</p>
                    <?php else: ?>
                        <div style="border-left:2px solid var(--gray-200);padding-left:16px;">
                            <?php foreach ($respuestas as $resp): ?>
                                <div style="position:relative;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--gray-100);">
                                    <div style="position:absolute;left:-23px;top:4px;width:12px;height:12px;border-radius:50%;background:var(--primary);border:2px solid white;"></div>
                                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                                        <span>
                                            <?php
                                            $tipoLabels = [
                                                'acuse_recibo'           => 'Acuse de recibo',
                                                'instrucciones_registro' => 'Instrucciones de registro',
                                                'respuesta_manual'       => 'Respuesta manual',
                                                'seguimiento'            => 'Seguimiento',
                                            ];
                                            ?>
                                            <span class="badge"><?= $tipoLabels[$resp['tipo']] ?? $resp['tipo'] ?></span>
                                            <small style="color:var(--gray-500);">por <?= e($resp['enviado_por']) ?></small>
                                        </span>
                                        <small style="color:var(--gray-500);"><?= date('d/m/Y H:i', strtotime($resp['created_at'])) ?></small>
                                    </div>
                                    <?php if (!empty($resp['asunto'])): ?>
                                        <div style="font-weight:600;font-size:13px;"><?= e($resp['asunto']) ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($resp['contenido'])): ?>
                                        <div style="color:var(--gray-600);font-size:13px;margin-top:4px;">
                                            <?= e(mb_substr($resp['contenido'], 0, 200)) ?>
                                            <?= mb_strlen($resp['contenido']) > 200 ? '...' : '' ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Formulario de respuesta manual -->
            <div class="card">
                <div class="card__header">
                    <h3 style="margin:0;">Enviar respuesta</h3>
                </div>
                <div class="card__body">
                    <form method="POST" action="<?= url('/admin/mensajes/' . $mensaje['id'] . '/responder') ?>">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label>Asunto</label>
                            <input type="text" name="asunto" class="form-control"
                                   value="Re: <?= e($mensaje['asunto']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Contenido</label>
                            <textarea name="contenido" class="form-control" rows="6" required
                                      placeholder="Escribe tu respuesta..."></textarea>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <small style="color:var(--gray-500);">Se enviara a: <?= e($mensaje['email']) ?></small>
                            <button type="submit" class="btn btn--primary"
                                    onclick="return confirm('Enviar respuesta a <?= e($mensaje['email']) ?>?')">
                                &#9993; Enviar respuesta
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar derecho -->
        <div>
            <!-- Estado -->
            <div class="card" style="margin-bottom:1rem;">
                <div class="card__header">
                    <h4 style="margin:0;">Estado</h4>
                </div>
                <div class="card__body">
                    <form method="POST" action="<?= url('/admin/mensajes/' . $mensaje['id'] . '/estado') ?>">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <select name="estado" class="form-control" id="selectEstado">
                                <?php
                                $estados = [
                                    'nuevo'      => 'Nuevo',
                                    'leido'      => 'Leido',
                                    'respondido' => 'Respondido',
                                    'convertido' => 'Convertido',
                                    'descartado' => 'Descartado',
                                ];
                                foreach ($estados as $val => $label):
                                ?>
                                    <option value="<?= $val ?>" <?= $mensaje['estado'] === $val ? 'selected' : '' ?>>
                                        <?= $label ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="comercioSelector" style="display:<?= $mensaje['estado'] === 'convertido' ? 'block' : 'none' ?>;">
                            <div class="form-group">
                                <label style="font-size:12px;">Comercio vinculado</label>
                                <select name="comercio_id" class="form-control">
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($comercios as $com): ?>
                                        <option value="<?= $com['id'] ?>"
                                            <?= ($mensaje['comercio_id'] ?? 0) == $com['id'] ? 'selected' : '' ?>>
                                            <?= e($com['nombre']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn--primary btn--sm" style="width:100%;">
                            Actualizar estado
                        </button>
                    </form>
                </div>
            </div>

            <!-- Notas -->
            <div class="card" style="margin-bottom:1rem;">
                <div class="card__header">
                    <h4 style="margin:0;">Notas internas</h4>
                </div>
                <div class="card__body">
                    <form method="POST" action="<?= url('/admin/mensajes/' . $mensaje['id'] . '/nota') ?>">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <textarea name="notas_admin" class="form-control" rows="4"
                                      placeholder="Notas internas..."><?= e($mensaje['notas_admin'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="btn btn--outline btn--sm" style="width:100%;">
                            Guardar nota
                        </button>
                    </form>
                </div>
            </div>

            <!-- Info rapida -->
            <div class="card">
                <div class="card__body" style="font-size:13px;">
                    <div style="margin-bottom:8px;">
                        <small style="color:var(--gray-500);">Respondido</small>
                        <div><?= $mensaje['respondido_at'] ? date('d/m/Y H:i', strtotime($mensaje['respondido_at'])) : 'No' ?></div>
                    </div>
                    <div style="margin-bottom:8px;">
                        <small style="color:var(--gray-500);">Convertido</small>
                        <div><?= $mensaje['convertido_at'] ? date('d/m/Y H:i', strtotime($mensaje['convertido_at'])) : 'No' ?></div>
                    </div>
                    <div>
                        <small style="color:var(--gray-500);">Respuestas enviadas</small>
                        <div><?= $mensaje['total_respuestas'] ?? 0 ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('selectEstado').addEventListener('change', function() {
    document.getElementById('comercioSelector').style.display =
        this.value === 'convertido' ? 'block' : 'none';
});
</script>
