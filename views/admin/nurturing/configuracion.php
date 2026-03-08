<div class="admin-page">
    <div class="admin-breadcrumb">
        <a href="<?= url('/admin/nurturing') ?>">Nurturing</a>
        <span>/</span>
        <span>Configuracion</span>
    </div>

    <?php if (!empty($_SESSION['flash']['success'])): ?>
        <div class="alert alert--success"><?= e($_SESSION['flash']['success']) ?></div>
        <?php unset($_SESSION['flash']['success']); ?>
    <?php endif; ?>

    <?php
    // Extraer valores de config agrupada en un array plano
    $vals = [];
    foreach ($config as $grupo => $items) {
        foreach ($items as $item) {
            $vals[$item['clave']] = $item['valor'];
        }
    }
    ?>

    <form method="POST" action="<?= url('/admin/nurturing/configuracion') ?>">
        <?= csrf_field() ?>

        <!-- General -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header"><h3 style="margin:0;">General</h3></div>
            <div class="card__body">
                <div class="form-group">
                    <label>
                        <input type="hidden" name="servicio_activo" value="0">
                        <input type="checkbox" name="servicio_activo" value="1"
                            <?= ($vals['servicio_activo'] ?? '0') === '1' ? 'checked' : '' ?>>
                        Servicio activo
                    </label>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label>Maximo de recordatorios</label>
                        <input type="number" name="max_recordatorios" class="form-control"
                               min="1" max="10" value="<?= e($vals['max_recordatorios'] ?? '4') ?>">
                    </div>
                    <div class="form-group">
                        <label>Dias entre recordatorios</label>
                        <input type="number" name="intervalo_dias" class="form-control"
                               min="1" max="30" value="<?= e($vals['intervalo_dias'] ?? '7') ?>">
                    </div>
                    <div class="form-group">
                        <label>Hora de envio (informativa)</label>
                        <input type="time" name="hora_envio" class="form-control"
                               value="<?= e($vals['hora_envio'] ?? '10:00') ?>">
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div class="form-group">
                        <label>Nombre remitente</label>
                        <input type="text" name="nombre_remitente" class="form-control"
                               value="<?= e($vals['nombre_remitente'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Email remitente</label>
                        <input type="email" name="email_remitente" class="form-control"
                               value="<?= e($vals['email_remitente'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Condiciones -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header"><h3 style="margin:0;">Condiciones de envio</h3></div>
            <div class="card__body">
                <div class="form-group">
                    <label>
                        <input type="hidden" name="solo_con_instrucciones" value="0">
                        <input type="checkbox" name="solo_con_instrucciones" value="1"
                            <?= ($vals['solo_con_instrucciones'] ?? '1') === '1' ? 'checked' : '' ?>>
                        Solo enviar a quienes recibieron instrucciones de registro
                    </label>
                </div>
                <div class="form-group">
                    <label>Estados que excluyen (separados por coma)</label>
                    <input type="text" name="estados_excluidos" class="form-control"
                           value="<?= e($vals['estados_excluidos'] ?? 'convertido,descartado') ?>"
                           placeholder="convertido,descartado">
                </div>
                <div class="form-group">
                    <label>Dias de espera antes del primer recordatorio</label>
                    <input type="number" name="dias_espera_primera" class="form-control"
                           min="1" max="30" value="<?= e($vals['dias_espera_primera'] ?? '7') ?>">
                </div>
            </div>
        </div>

        <!-- Desuscripcion -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header"><h3 style="margin:0;">Desuscripcion</h3></div>
            <div class="card__body">
                <div class="form-group">
                    <label>
                        <input type="hidden" name="desuscripcion_activa" value="0">
                        <input type="checkbox" name="desuscripcion_activa" value="1"
                            <?= ($vals['desuscripcion_activa'] ?? '1') === '1' ? 'checked' : '' ?>>
                        Incluir link de desuscripcion en emails
                    </label>
                </div>
                <div class="form-group">
                    <label>URL base de desuscripcion</label>
                    <input type="text" name="url_desuscripcion" class="form-control"
                           value="<?= e($vals['url_desuscripcion'] ?? '') ?>"
                           placeholder="Se auto-genera si se deja vacio">
                </div>
                <div class="form-group">
                    <label>Texto del link</label>
                    <input type="text" name="texto_desuscripcion" class="form-control"
                           value="<?= e($vals['texto_desuscripcion'] ?? 'Si no deseas recibir mas correos, haz clic aqui') ?>">
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn--primary">Guardar configuracion</button>
    </form>
</div>
