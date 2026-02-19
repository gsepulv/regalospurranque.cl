<div class="admin-page">
    <div class="admin-page__header">
        <h1>Notificaciones</h1>
        <div class="toolbar">
            <a href="<?= url('/admin/notificaciones/log') ?>" class="btn btn--outline">
                Ver historial de envíos
            </a>
        </div>
    </div>

    <!-- Configuración General -->
    <form method="POST" action="<?= url('/admin/notificaciones/config') ?>">
        <?= csrf_field() ?>

        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header">
                <h3>Configuración general</h3>
            </div>
            <div class="card__body">
                <div class="form-group">
                    <label class="toggle">
                        <input type="checkbox" name="notificaciones_activas" value="1"
                            <?= ($config['notificaciones_activas'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="toggle__label">Notificaciones activas</span>
                    </label>
                    <small class="form-help">Desactiva todas las notificaciones por email del sistema</small>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Email remitente (From)</label>
                        <input type="email" name="email_from" class="form-control"
                               value="<?= e($config['email_from'] ?? '') ?>"
                               placeholder="no-reply@regalos.purranque.info">
                        <small class="form-help">Dejar vacío para usar el predeterminado del servidor</small>
                    </div>
                    <div class="form-group">
                        <label>Email de respuesta (Reply-To)</label>
                        <input type="email" name="email_reply_to" class="form-control"
                               value="<?= e($config['email_reply_to'] ?? '') ?>"
                               placeholder="contacto@regalos.purranque.info">
                    </div>
                </div>
            </div>
        </div>

        <!-- Eventos de Reseñas -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header">
                <h3>Reseñas</h3>
            </div>
            <div class="card__body">
                <div class="form-group">
                    <label class="toggle">
                        <input type="checkbox" name="notif_nueva_resena" value="1"
                            <?= ($config['notif_nueva_resena'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="toggle__label">Nueva reseña recibida</span>
                    </label>
                    <small class="form-help">Notifica a los administradores cuando se recibe una nueva reseña</small>
                </div>

                <div class="form-group">
                    <label class="toggle">
                        <input type="checkbox" name="notif_resena_aprobada" value="1"
                            <?= ($config['notif_resena_aprobada'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="toggle__label">Reseña aprobada</span>
                    </label>
                    <small class="form-help">Notifica al autor cuando su reseña es aprobada</small>
                </div>

                <div class="form-group">
                    <label class="toggle">
                        <input type="checkbox" name="notif_resena_rechazada" value="1"
                            <?= ($config['notif_resena_rechazada'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="toggle__label">Reseña rechazada</span>
                    </label>
                    <small class="form-help">Notifica al autor cuando su reseña es rechazada</small>
                </div>

                <div class="form-group">
                    <label class="toggle">
                        <input type="checkbox" name="notif_resena_respuesta" value="1"
                            <?= ($config['notif_resena_respuesta'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="toggle__label">Respuesta a reseña</span>
                    </label>
                    <small class="form-help">Notifica al autor cuando el comercio responde su reseña</small>
                </div>

                <div class="form-group">
                    <label class="toggle">
                        <input type="checkbox" name="notif_reporte_resena" value="1"
                            <?= ($config['notif_reporte_resena'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="toggle__label">Reseña reportada</span>
                    </label>
                    <small class="form-help">Notifica a los administradores cuando se reporta una reseña</small>
                </div>
            </div>
        </div>

        <!-- Eventos de Comercios -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header">
                <h3>Comercios</h3>
            </div>
            <div class="card__body">
                <div class="form-group">
                    <label class="toggle">
                        <input type="checkbox" name="notif_nuevo_comercio" value="1"
                            <?= ($config['notif_nuevo_comercio'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="toggle__label">Nuevo comercio registrado</span>
                    </label>
                    <small class="form-help">Notifica a los administradores cuando se crea un nuevo comercio</small>
                </div>

                <div class="form-group">
                    <label class="toggle">
                        <input type="checkbox" name="notif_bienvenida_comercio" value="1"
                            <?= ($config['notif_bienvenida_comercio'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="toggle__label">Email de bienvenida</span>
                    </label>
                    <small class="form-help">Envía un email de bienvenida al comercio cuando es registrado</small>
                </div>
            </div>
        </div>

        <!-- Eventos del Sistema -->
        <div class="card" style="margin-bottom:1.5rem;">
            <div class="card__header">
                <h3>Sistema</h3>
            </div>
            <div class="card__body">
                <div class="form-group">
                    <label class="toggle">
                        <input type="checkbox" name="notif_backup" value="1"
                            <?= ($config['notif_backup'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span class="toggle__label">Backup completado</span>
                    </label>
                    <small class="form-help">Notifica cuando se completa un backup (puede generar muchos emails)</small>
                </div>

                <div class="form-group">
                    <label class="toggle">
                        <input type="checkbox" name="notif_error_sistema" value="1"
                            <?= ($config['notif_error_sistema'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="toggle__label">Error de sistema</span>
                    </label>
                    <small class="form-help">Notifica a los administradores cuando ocurre un error crítico</small>
                </div>

                <div class="form-group">
                    <label class="toggle">
                        <input type="checkbox" name="notif_resumen_semanal" value="1"
                            <?= ($config['notif_resumen_semanal'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="toggle__label">Resumen semanal</span>
                    </label>
                    <small class="form-help">Envía un resumen semanal de actividad a los administradores</small>
                </div>

                <div class="form-group">
                    <label class="toggle">
                        <input type="checkbox" name="notif_fecha_proxima" value="1"
                            <?= ($config['notif_fecha_proxima'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="toggle__label">Fecha especial próxima</span>
                    </label>
                    <small class="form-help">Notifica cuando una fecha especial está próxima (7 días antes)</small>
                </div>
            </div>
        </div>

        <div class="toolbar">
            <button type="submit" class="btn btn--primary">Guardar configuración</button>
        </div>
    </form>

    <!-- Email de prueba -->
    <div class="card" style="margin-top:2rem;">
        <div class="card__header">
            <h3>Email de prueba</h3>
        </div>
        <div class="card__body">
            <form method="POST" action="<?= url('/admin/notificaciones/test') ?>">
                <?= csrf_field() ?>
                <div class="form-row" style="align-items:flex-end;">
                    <div class="form-group" style="flex:1;">
                        <label>Enviar email de prueba a:</label>
                        <input type="email" name="test_email" class="form-control"
                               value="<?= e($admin['email'] ?? '') ?>"
                               placeholder="tu@email.com" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn--outline">Enviar prueba</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
