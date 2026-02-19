<?php
/**
 * Admin - Configuración de reseñas
 * Variables: $config
 */
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/resenas') ?>">Reseñas</a> &rsaquo;
    <span>Configuración</span>
</div>

<div class="toolbar">
    <h2 style="margin:0;flex:1">Configuración de Reseñas</h2>
    <a href="<?= url('/admin/resenas') ?>" class="btn btn--outline btn--sm">&larr; Volver a reseñas</a>
</div>

<form method="POST" action="<?= url('/admin/resenas/configuracion') ?>">
    <?= csrf_field() ?>

    <div class="admin-card" style="margin-bottom:var(--spacing-6)">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">General</h3>

            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="resenas_activas" value="1" <?= $config['resenas_activas'] ? 'checked' : '' ?>>
                    Reseñas activas
                </label>
                <small class="form-hint">Permitir a los usuarios enviar reseñas en los comercios.</small>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="moderacion" value="1" <?= $config['moderacion'] ? 'checked' : '' ?>>
                    Moderación previa
                </label>
                <small class="form-hint">Las reseñas deben ser aprobadas antes de publicarse. Si se desactiva, las reseñas se publican automáticamente.</small>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="permitir_anonimas" value="1" <?= $config['permitir_anonimas'] ? 'checked' : '' ?>>
                    Permitir reseñas anónimas
                </label>
                <small class="form-hint">Permitir enviar reseñas sin proporcionar email.</small>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="permitir_respuesta" value="1" <?= $config['permitir_respuesta'] ? 'checked' : '' ?>>
                    Permitir respuestas
                </label>
                <small class="form-hint">Permitir que los administradores respondan a las reseñas.</small>
            </div>

            <div class="form-group">
                <label class="form-label">
                    <input type="checkbox" name="notificar_nueva" value="1" <?= $config['notificar_nueva'] ? 'checked' : '' ?>>
                    Notificar nuevas reseñas
                </label>
                <small class="form-hint">Mostrar notificación en el dashboard cuando haya reseñas pendientes.</small>
            </div>
        </div>
    </div>

    <div class="admin-card" style="margin-bottom:var(--spacing-6)">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Límites</h3>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-4)">
                <div class="form-group">
                    <label class="form-label">Mínimo de caracteres</label>
                    <input type="number"
                           name="min_caracteres"
                           class="form-control"
                           value="<?= e($config['min_caracteres']) ?>"
                           min="0"
                           max="500"
                           style="width:120px">
                    <small class="form-hint">Mínimo de caracteres en el comentario (0 = sin mínimo).</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Máximo de caracteres</label>
                    <input type="number"
                           name="max_caracteres"
                           class="form-control"
                           value="<?= e($config['max_caracteres']) ?>"
                           min="100"
                           max="10000"
                           style="width:120px">
                    <small class="form-hint">Máximo de caracteres en el comentario.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Máximo reseñas por IP/día</label>
                    <input type="number"
                           name="max_por_ip_dia"
                           class="form-control"
                           value="<?= e($config['max_por_ip_dia']) ?>"
                           min="1"
                           max="100"
                           style="width:120px">
                    <small class="form-hint">Límite de reseñas por dirección IP por día para evitar spam.</small>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;gap:var(--spacing-3)">
        <button type="submit" class="btn btn--primary">Guardar configuración</button>
        <a href="<?= url('/admin/resenas') ?>" class="btn btn--outline">Cancelar</a>
    </div>
</form>
