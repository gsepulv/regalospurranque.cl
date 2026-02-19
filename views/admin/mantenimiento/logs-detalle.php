<?php
/**
 * Admin - Mantenimiento > Logs > Detalle
 * Variables: $log (single admin_log row with all fields)
 */

$accion = strtolower($log['accion'] ?? '');
$accionBadge = 'badge';
if (in_array($accion, ['crear', 'create'])) {
    $accionBadge = 'badge log-badge--crear';
} elseif (in_array($accion, ['editar', 'update', 'actualizar'])) {
    $accionBadge = 'badge log-badge--editar';
} elseif (in_array($accion, ['eliminar', 'delete', 'borrar'])) {
    $accionBadge = 'badge log-badge--eliminar';
} elseif ($accion === 'login') {
    $accionBadge = 'badge log-badge--login';
} elseif ($accion === 'toggle') {
    $accionBadge = 'badge log-badge--toggle';
}

$datosAntes   = !empty($log['datos_antes'])   ? json_decode($log['datos_antes'], true)   : null;
$datosDespues = !empty($log['datos_despues'])  ? json_decode($log['datos_despues'], true)  : null;
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <a href="<?= url('/admin/mantenimiento/backups') ?>">Mantenimiento</a> &rsaquo;
    <a href="<?= url('/admin/mantenimiento/logs') ?>">Logs</a> &rsaquo;
    <span>Detalle #<?= e($log['id']) ?></span>
</div>

<div class="toolbar">
    <h2 style="margin:0;flex:1">
        Log #<?= e($log['id']) ?>
        <span class="<?= $accionBadge ?>" style="font-size:0.75rem;vertical-align:middle"><?= e($log['accion'] ?? '') ?></span>
    </h2>
    <a href="<?= url('/admin/mantenimiento/logs') ?>" class="btn btn--outline btn--sm">&larr; Volver a Logs</a>
</div>

<!-- Detail Card -->
<div class="admin-card" style="margin-bottom:var(--spacing-6)">
    <div style="padding:var(--spacing-6)">
        <table class="log-detail-table">
            <tr>
                <th>ID</th>
                <td>#<?= e($log['id']) ?></td>
            </tr>
            <tr>
                <th>Fecha</th>
                <td><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
            </tr>
            <tr>
                <th>Usuario</th>
                <td>
                    <?= e($log['usuario_nombre'] ?? 'Sistema') ?>
                    <?php if (!empty($log['usuario_id'])): ?>
                        <small style="color:var(--color-gray)">(ID: <?= e($log['usuario_id']) ?>)</small>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>M&oacute;dulo</th>
                <td><?= e($log['modulo'] ?? '') ?></td>
            </tr>
            <tr>
                <th>Acci&oacute;n</th>
                <td><span class="<?= $accionBadge ?>"><?= e($log['accion'] ?? '') ?></span></td>
            </tr>
            <tr>
                <th>Entidad</th>
                <td>
                    <?php if (!empty($log['entidad_tipo'])): ?>
                        <?= e($log['entidad_tipo']) ?>
                        <?php if (!empty($log['entidad_id'])): ?>
                            <span style="color:var(--color-gray)">#<?= e($log['entidad_id']) ?></span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:var(--color-gray)">&mdash;</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>Detalle</th>
                <td><?= e($log['detalle'] ?? '') ?></td>
            </tr>
            <tr>
                <th>IP</th>
                <td><?= e($log['ip'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <th>User Agent</th>
                <td><small><?= e($log['user_agent'] ?? 'N/A') ?></small></td>
            </tr>
        </table>
    </div>
</div>

<!-- Datos Anteriores -->
<?php if ($datosAntes !== null): ?>
    <div class="admin-card" style="margin-bottom:var(--spacing-6)">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Datos Anteriores</h3>
            <div class="json-viewer">
                <pre><code><?= formatJsonHtml($log['datos_antes']) ?></code></pre>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Datos Posteriores -->
<?php if ($datosDespues !== null): ?>
    <div class="admin-card" style="margin-bottom:var(--spacing-6)">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Datos Posteriores</h3>
            <div class="json-viewer">
                <pre><code><?= formatJsonHtml($log['datos_despues']) ?></code></pre>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Diff: Cambios -->
<?php if ($datosAntes !== null && $datosDespues !== null): ?>
    <div class="admin-card" style="margin-bottom:var(--spacing-6)">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Cambios</h3>
            <?php
            $allKeys = array_unique(array_merge(array_keys($datosAntes), array_keys($datosDespues)));
            sort($allKeys);
            $hasDiff = false;
            ?>
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Campo</th>
                            <th>Antes</th>
                            <th>Despu&eacute;s</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allKeys as $key): ?>
                            <?php
                            $antes   = $datosAntes[$key]   ?? null;
                            $despues = $datosDespues[$key]  ?? null;

                            // Convert to comparable strings
                            $antesStr   = is_array($antes)   ? json_encode($antes, JSON_UNESCAPED_UNICODE)   : (string)$antes;
                            $despuesStr = is_array($despues)  ? json_encode($despues, JSON_UNESCAPED_UNICODE) : (string)$despues;

                            if ($antesStr === $despuesStr) continue;
                            $hasDiff = true;
                            ?>
                            <tr>
                                <td><strong><?= e($key) ?></strong></td>
                                <td>
                                    <?php if ($antes === null): ?>
                                        <span class="diff-added">(nuevo)</span>
                                    <?php else: ?>
                                        <span class="diff-removed"><?= e($antesStr) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($despues === null): ?>
                                        <span class="diff-removed">(eliminado)</span>
                                    <?php else: ?>
                                        <span class="diff-added"><?= e($despuesStr) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$hasDiff): ?>
                            <tr>
                                <td colspan="3" style="text-align:center;color:var(--color-gray);padding:var(--spacing-4)">
                                    No se detectaron diferencias entre los datos.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
/**
 * Formats a JSON string into syntax-highlighted HTML.
 */
function formatJsonHtml(string $jsonString): string {
    $decoded = json_decode($jsonString);
    if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
        return e($jsonString);
    }
    $pretty = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    // Escape HTML first
    $escaped = htmlspecialchars($pretty, ENT_QUOTES, 'UTF-8');
    // Highlight JSON keys (orange)
    $escaped = preg_replace('/&quot;([^&]+?)&quot;(\s*:)/', '<span class="json-key">&quot;$1&quot;</span>$2', $escaped);
    // Highlight string values (green)
    $escaped = preg_replace('/(:\s*)&quot;([^&]*?)&quot;/', '$1<span class="json-string">&quot;$2&quot;</span>', $escaped);
    // Highlight numbers (blue)
    $escaped = preg_replace('/(:\s*)(\d+\.?\d*)/', '$1<span class="json-number">$2</span>', $escaped);
    // Highlight booleans and null (purple)
    $escaped = preg_replace('/\b(true|false|null)\b/', '<span class="json-bool">$1</span>', $escaped);
    return $escaped;
}
?>

<style>
/* Detail table */
.log-detail-table {
    width: 100%;
    border-collapse: collapse;
}
.log-detail-table th,
.log-detail-table td {
    padding: var(--spacing-3) var(--spacing-4);
    border-bottom: 1px solid var(--color-border, #e5e7eb);
    font-size: 0.875rem;
    vertical-align: top;
}
.log-detail-table th {
    width: 140px;
    color: var(--color-gray);
    font-weight: 600;
    text-align: left;
    white-space: nowrap;
}

/* Action badges */
.log-badge--crear {
    background: #dcfce7;
    color: #166534;
}
.log-badge--editar {
    background: #dbeafe;
    color: #1e40af;
}
.log-badge--eliminar {
    background: #fee2e2;
    color: #991b1b;
}
.log-badge--login {
    background: #f3f4f6;
    color: #374151;
}
.log-badge--toggle {
    background: #fef9c3;
    color: #854d0e;
}

/* JSON viewer */
.json-viewer {
    background: #1e1e2e;
    border-radius: var(--radius-md, 8px);
    overflow-x: auto;
}
.json-viewer pre {
    margin: 0;
    padding: var(--spacing-4);
    font-size: 0.8125rem;
    line-height: 1.6;
    color: #cdd6f4;
}
.json-viewer code {
    font-family: 'Fira Code', 'Consolas', 'Monaco', monospace;
}
.json-key {
    color: #fab387;
}
.json-string {
    color: #a6e3a1;
}
.json-number {
    color: #89b4fa;
}
.json-bool {
    color: #cba6f7;
}

/* Diff display */
.diff-removed {
    background: #fee2e2;
    color: #991b1b;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.8125rem;
    word-break: break-all;
}
.diff-added {
    background: #dcfce7;
    color: #166534;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.8125rem;
    word-break: break-all;
}
</style>
