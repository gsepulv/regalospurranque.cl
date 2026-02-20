<?php
/**
 * Página de compartir con preview optimizado para redes sociales
 * Variables: $tipo, $slug, $data, $shareUrl, $shareTitle, $shareDescription
 */
$encodedUrl = urlencode($shareUrl);
$encodedTitle = urlencode($shareTitle);
$encodedDesc = urlencode($shareDescription);
?>

<section class="section">
    <div class="container">
        <div class="share-page">

            <!-- Preview del contenido -->
            <div class="share-preview">
                <?php if ($tipo === 'comercio'): ?>
                    <?php if (!empty($data['portada'])): ?>
                        <?= picture('img/portadas/' . $data['portada'], $data['nombre'], 'share-preview__img', false) ?>
                    <?php endif; ?>
                    <div class="share-preview__body">
                        <h1><?= e($data['nombre']) ?></h1>
                        <?php if (!empty($data['descripcion'])): ?>
                            <p><?= e(truncate($data['descripcion'], 200)) ?></p>
                        <?php endif; ?>
                        <a href="<?= url('/comercio/' . $data['slug']) ?>" class="btn btn--primary">Ver comercio</a>
                    </div>
                <?php elseif ($tipo === 'noticia'): ?>
                    <?php if (!empty($data['imagen'])): ?>
                        <?= picture('img/noticias/' . $data['imagen'], $data['titulo'], 'share-preview__img', false) ?>
                    <?php endif; ?>
                    <div class="share-preview__body">
                        <h1><?= e($data['titulo']) ?></h1>
                        <?php if (!empty($data['extracto'])): ?>
                            <p><?= e($data['extracto']) ?></p>
                        <?php endif; ?>
                        <a href="<?= url('/noticia/' . $data['slug']) ?>" class="btn btn--primary">Leer más</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Botones de compartir -->
            <div class="share-buttons">
                <h2>Compartir en redes sociales</h2>
                <div class="share-buttons__grid">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl ?>"
                       target="_blank" rel="noopener"
                       class="share-btn share-btn--facebook"
                       onclick="trackShare('facebook')">
                        <span class="share-btn__icon">f</span>
                        Facebook
                    </a>

                    <a href="https://twitter.com/intent/tweet?url=<?= $encodedUrl ?>&text=<?= $encodedTitle ?>"
                       target="_blank" rel="noopener"
                       class="share-btn share-btn--twitter"
                       onclick="trackShare('twitter')">
                        <span class="share-btn__icon">X</span>
                        Twitter/X
                    </a>

                    <a href="https://wa.me/?text=<?= urlencode($shareTitle . ' — ' . $shareUrl) ?>"
                       target="_blank" rel="noopener"
                       class="share-btn share-btn--whatsapp"
                       onclick="trackShare('whatsapp')">
                        <span class="share-btn__icon">W</span>
                        WhatsApp
                    </a>

                    <button class="share-btn share-btn--copy" onclick="copyLink()">
                        <span class="share-btn__icon">&#128279;</span>
                        Copiar enlace
                    </button>
                </div>

                <div id="copySuccess" class="share-copy-msg" style="display:none">
                    Enlace copiado al portapapeles
                </div>
            </div>

        </div>
    </div>
</section>

<script>
function trackShare(red) {
    fetch('<?= url('/api/share-track') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            tipo: '<?= e($tipo) ?>',
            url: '<?= e($shareUrl) ?>',
            red: red,
            comercio_id: <?= ($tipo === 'comercio' && !empty($data['id'])) ? (int)$data['id'] : 'null' ?>
        })
    });
}

function copyLink() {
    var url = '<?= e($shareUrl) ?>';
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(function() {
            showCopySuccess();
        });
    } else {
        var textarea = document.createElement('textarea');
        textarea.value = url;
        textarea.style.position = 'fixed';
        textarea.style.left = '-9999px';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showCopySuccess();
    }
    trackShare('copy');
}

function showCopySuccess() {
    var msg = document.getElementById('copySuccess');
    msg.style.display = 'block';
    setTimeout(function() { msg.style.display = 'none'; }, 3000);
}
</script>
