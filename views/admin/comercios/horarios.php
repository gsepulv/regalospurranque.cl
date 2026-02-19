<?php
/**
 * Horarios del comercio - Admin CRUD
 * Variables: $comercio, $horarios (indexed by day 0-6)
 */
$dias = ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
?>

<div class="admin-breadcrumb">
    <a href="<?= url('/admin') ?>">Dashboard</a>
    <span>/</span>
    <a href="<?= url('/admin/comercios') ?>">Comercios</a>
    <span>/</span>
    <a href="<?= url('/admin/comercios/editar/' . $comercio['id']) ?>"><?= e($comercio['nombre']) ?></a>
    <span>/</span>
    <span>Horarios</span>
</div>

<h2 style="margin-bottom:1.25rem">Horarios de <?= e($comercio['nombre']) ?></h2>

<div class="admin-card">
    <div class="admin-card__header">
        <h3 class="admin-card__title">Horarios de atencion</h3>
    </div>
    <div class="admin-card__body" style="padding:0">
        <form method="POST" action="<?= url('/admin/comercios/' . $comercio['id'] . '/horarios') ?>">
            <?= csrf_field() ?>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Dia</th>
                            <th>Hora apertura</th>
                            <th>Hora cierre</th>
                            <th>Cerrado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($dia = 0; $dia <= 6; $dia++): ?>
                            <?php
                            $h = $horarios[$dia] ?? [];
                            $cerrado  = !empty($h['cerrado']);
                            $apertura = $h['hora_apertura'] ?? '';
                            $cierre   = $h['hora_cierre'] ?? '';
                            ?>
                            <tr>
                                <td>
                                    <strong><?= $dias[$dia] ?></strong>
                                </td>
                                <td>
                                    <input type="time"
                                           name="hora_apertura[<?= $dia ?>]"
                                           class="form-control"
                                           value="<?= e($apertura) ?>"
                                           style="max-width:160px"
                                           <?= $cerrado ? 'disabled' : '' ?>>
                                </td>
                                <td>
                                    <input type="time"
                                           name="hora_cierre[<?= $dia ?>]"
                                           class="form-control"
                                           value="<?= e($cierre) ?>"
                                           style="max-width:160px"
                                           <?= $cerrado ? 'disabled' : '' ?>>
                                </td>
                                <td>
                                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer">
                                        <input type="checkbox"
                                               name="cerrado[<?= $dia ?>]"
                                               value="1"
                                               class="cerrado-checkbox"
                                               data-dia="<?= $dia ?>"
                                               <?= $cerrado ? 'checked' : '' ?>>
                                        Cerrado
                                    </label>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                </table>
            </div>

            <div style="padding:1rem 1.25rem;border-top:1px solid var(--color-border)">
                <button type="submit" class="btn btn--primary">Guardar horarios</button>
                <a href="<?= url('/admin/comercios') ?>" class="btn btn--outline">Volver</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Disable/enable time inputs when "Cerrado" is toggled
    document.querySelectorAll('.cerrado-checkbox').forEach(function (cb) {
        cb.addEventListener('change', function () {
            var dia = cb.getAttribute('data-dia');
            var row = cb.closest('tr');
            var timeInputs = row.querySelectorAll('input[type="time"]');

            timeInputs.forEach(function (input) {
                input.disabled = cb.checked;
                if (cb.checked) {
                    input.value = '';
                }
            });
        });
    });
});
</script>
