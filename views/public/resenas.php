<?php
/**
 * Vista "Mis Reseñas" — Consultar reseñas por email
 * Variables: $email, $resenas
 */
?>

<section class="section">
    <div class="container">
        <div class="page-header">
            <h1>Mis Reseñas</h1>
            <p class="text-muted">Consulta las reseñas que has publicado ingresando tu correo electrónico</p>
        </div>

        <!-- Formulario de consulta -->
        <div class="resenas-lookup">
            <form action="<?= url('/mis-resenas') ?>" method="GET" class="resenas-lookup__form">
                <div class="search-input-group">
                    <input type="email"
                           name="email"
                           value="<?= e($email ?? '') ?>"
                           placeholder="Tu correo electrónico..."
                           class="form-control"
                           required>
                    <button type="submit" class="btn btn--primary">Consultar</button>
                </div>
            </form>
        </div>

        <!-- Resultados -->
        <?php if (!empty($email)): ?>
            <?php if (!empty($resenas)): ?>
                <p class="results-count"><?= count($resenas) ?> reseña<?= count($resenas) != 1 ? 's' : '' ?> encontrada<?= count($resenas) != 1 ? 's' : '' ?></p>

                <div class="resenas-list">
                    <?php foreach ($resenas as $r): ?>
                        <div class="resena-card resena-card--full">
                            <div class="resena-card__header">
                                <a href="<?= url('/comercio/' . $r['comercio_slug']) ?>" class="resena-card__comercio">
                                    <?= e($r['comercio_nombre']) ?>
                                </a>
                                <span class="resena-card__stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?= $i <= $r['calificacion'] ? 'star--filled' : '' ?>">&#9733;</span>
                                    <?php endfor; ?>
                                </span>
                                <span class="text-muted text-sm"><?= fecha_es($r['created_at']) ?></span>
                            </div>

                            <?php if (!empty($r['comentario'])): ?>
                                <p class="resena-card__comentario"><?= nl2br(e($r['comentario'])) ?></p>
                            <?php endif; ?>

                            <div class="resena-card__status">
                                <?php if ($r['estado'] === 'aprobada'): ?>
                                    <span class="badge badge--success">Aprobada</span>
                                <?php elseif ($r['estado'] === 'pendiente'): ?>
                                    <span class="badge badge--warning">Pendiente de revisión</span>
                                <?php else: ?>
                                    <span class="badge badge--danger">Rechazada</span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($r['respuesta_comercio'])): ?>
                                <div class="resena-card__respuesta">
                                    <strong>Respuesta del comercio:</strong>
                                    <p><?= nl2br(e($r['respuesta_comercio'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <p>No se encontraron reseñas asociadas a este correo electrónico.</p>
                    <a href="<?= url('/buscar') ?>" class="btn btn--primary">Explorar comercios</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
