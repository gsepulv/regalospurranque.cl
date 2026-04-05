<?php
/**
 * Vista detalle de comercio
 * Variables: $comercio, $inactivo, $fotos, $horarios, $resenas, $distribucion, $relacionados, $banners
 */
$pageType = 'comercio';
$dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
?>
<script>if(typeof fbq==='function')fbq('track','ViewContent',{content_name:<?= json_encode($comercio['nombre'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,content_type:'comercio'});</script>
<?php
$hoy = (int) date('w');
?>

<section class="section">
    <div class="container">
        <?php if (!empty($inactivo)): ?>
            <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.25rem;text-align:center">
                <p style="margin:0;color:#991B1B;font-size:0.95rem;font-weight:600">
                    Este comercio no est&aacute; activo actualmente. La informaci&oacute;n puede no estar actualizada.
                </p>
            </div>
        <?php endif; ?>
        <div class="comercio-layout">

            <!-- Contenido principal -->
            <div class="comercio-main">

                <!-- Cabecera: portada + info basica -->
                <div class="comercio-header">
                    <?php if (!empty($comercio['portada'])): ?>
                        <?= picture('img/portadas/' . $comercio['portada'], $comercio['nombre'], 'comercio-header__portada', false, 1200, 400) ?>
                    <?php else: ?>
                        <div class="comercio-header__portada comercio-header__portada--placeholder">
                            <span><?= mb_substr($comercio['nombre'], 0, 1) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="comercio-header__info">
                        <?php if (!empty($comercio['logo'])): ?>
                            <?= picture('img/logos/' . $comercio['logo'], 'Logo ' . $comercio['nombre'], 'comercio-header__logo', false, 100, 100) ?>
                        <?php endif; ?>

                        <div>
                            <h1 class="comercio-header__nombre"><?= e($comercio['nombre']) ?></h1>

                            <div class="flex flex--wrap gap-2 mb-2">
                                <?php
                                $_plan = $comercio['plan'] ?? 'freemium';
                                $_validado = !empty($comercio['validado']);
                                ?>
                                <?php if ($_plan === 'sponsor'): ?>
                                    <span class="badge badge--plan badge--sponsor">&#127942; Sponsor</span>
                                <?php elseif ($_plan === 'premium'): ?>
                                    <span class="badge badge--plan badge--premium">&#11088; Premium</span>
                                <?php elseif ($_plan === 'basico'): ?>
                                    <span class="badge badge--plan badge--basico">&#9989; Básico</span>
                                <?php elseif ($_plan === 'banner'): ?>
                                    <span class="badge badge--plan badge--banner">&#128226; Banner</span>
                                <?php endif; ?>
                                <?php if ($_validado || !empty($comercio['activo'])): ?>
                                    <span class="badge badge--validado" style="background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;font-weight:600;padding:0.25em 0.75em;border-radius:999px;font-size:0.85rem">&#10003; Verificado</span>
                                <?php endif; ?>
                                <?php if ($comercio['destacado']): ?>
                                    <span class="badge badge--warning">Destacado</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($comercio['calificación_promedio']): ?>
                                <div class="comercio-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star <?= $i <= round($comercio['calificación_promedio']) ? 'star--filled' : '' ?>">&#9733;</span>
                                    <?php endfor; ?>
                                    <span class="text-muted text-sm">
                                        <?= $comercio['calificación_promedio'] ?> (<?= $comercio['total_resenas'] ?> reseña<?= $comercio['total_resenas'] != 1 ? 's' : '' ?>)
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Categorías -->
                <?php if (!empty($comercio['categorias'])): ?>
                    <div class="flex flex--wrap gap-2 mb-3">
                        <?php foreach ($comercio['categorias'] as $cat): ?>
                            <a href="<?= url('/categoria/' . $cat['slug']) ?>" class="badge badge--primary"><?= e($cat['nombre']) ?></a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php // Profiles: after_title position ?>
                <?php $position = 'after_title'; include BASE_PATH . '/views/partials/social-profiles.php'; ?>

                <?php // Share: above_content position ?>
                <?php
                $sharePageType = 'comercio';
                $sharePosition = 'above_content';
                $shareTitle = $comercio['nombre'];
                $shareDescription = $comercio['descripcion'] ?? '';
                $shareUrl = url('/comercio/' . $comercio['slug']);
                $shareSlug = $comercio['slug'];
                include BASE_PATH . '/views/partials/share-buttons.php';
                ?>

                <!-- Descripción -->
                <?php if (!empty($comercio['descripcion'])): ?>
                    <div class="comercio-section">
                        <h2>Sobre nosotros</h2>
                        <p><?= nl2br(e($comercio['descripcion'])) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Sobre este negocio (badges de confianza) -->
                <?php
                $badges_confianza = [];
                if (!empty($comercio['activo'])) $badges_confianza[] = ['icon' => '&#10003;', 'text' => 'Negocio verificado manualmente'];
                if (!empty($comercio['whatsapp'])) $badges_confianza[] = ['icon' => '&#128172;', 'text' => 'Responde por WhatsApp'];
                if (!empty($comercio['email'])) $badges_confianza[] = ['icon' => '&#9993;', 'text' => 'Responde por correo electrónico'];
                if (!empty($comercio['direccion'])) $badges_confianza[] = ['icon' => '&#128205;', 'text' => 'Negocio local en Purranque'];
                if (!empty($comercio['sitio_web'])) $badges_confianza[] = ['icon' => '&#127760;', 'text' => 'Tiene sitio web'];
                if (!empty($comercio['delivery_local'])) $badges_confianza[] = ['icon' => '&#128666;', 'text' => 'Ofrece delivery en Purranque y alrededores'];
                if (!empty($comercio['envios_chile'])) $badges_confianza[] = ['icon' => '&#128230;', 'text' => 'Realiza envíos a todo Chile'];
                ?>
                <?php if (!empty($badges_confianza)): ?>
                    <div class="comercio-section">
                        <h2>Sobre este negocio</h2>
                        <div style="border:1px solid #e0e0e0;border-radius:8px;padding:16px">
                            <?php foreach ($badges_confianza as $bc): ?>
                                <div style="display:flex;align-items:center;gap:8px;padding:6px 0;font-size:0.95rem;color:#444">
                                    <span style="font-size:1.1rem;flex-shrink:0"><?= $bc['icon'] ?></span>
                                    <span><?= $bc['text'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Cat&aacute;logo de productos -->
                <style>
                .catalogo-header{background:#f8f9fa;border-left:4px solid #4caf50;padding:20px;border-radius:0 8px 8px 0;margin-bottom:16px}
                .catalogo-titulo{font-size:1.5rem;font-weight:700;margin:0;color:#333}
                .catalogo-subtitulo{font-size:0.9rem;color:#666;margin-top:4px}
                .catalogo-filtros{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap}
                .catalogo-filtro{padding:6px 14px;border-radius:20px;border:1px solid #ddd;background:white;cursor:pointer;font-size:0.8rem;transition:all 0.2s;font-family:inherit;font-weight:600}
                .catalogo-filtro.activo{background:#4caf50;color:white;border-color:#4caf50}
                .catalogo-filtro:hover{border-color:#4caf50}
                .acordeon-item{border-bottom:1px solid #eee}
                .acordeon-item[data-estado="vendido"] .acordeon-nombre,.acordeon-item[data-estado="agotado"] .acordeon-nombre{color:#999}
                .acordeon-header{display:flex;align-items:center;padding:14px 16px;cursor:pointer;transition:background 0.2s;user-select:none}
                .acordeon-header:hover{background:#f9f9f9}
                .acordeon-thumb{width:60px;height:60px;border-radius:50%;object-fit:cover;margin-right:14px;flex-shrink:0;border:2px solid #f0f0f0}
                .acordeon-thumb-placeholder{width:60px;height:60px;border-radius:50%;background:#e0e0e0;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#999;margin-right:14px;flex-shrink:0;font-weight:700}
                .acordeon-nombre{flex:1;font-weight:600;font-size:1rem;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
                .acordeon-precio{color:#4caf50;font-weight:700;margin-right:12px;font-size:1rem;white-space:nowrap}
                .acordeon-badges{display:flex;gap:6px;margin-right:12px}
                .acordeon-badge{font-size:0.7rem;padding:2px 8px;border-radius:12px;white-space:nowrap}
                .acordeon-badge--disponible{background:#e8f5e9;color:#2e7d32}
                .acordeon-badge--vendido{background:#ffebee;color:#c62828}
                .acordeon-badge--reservado{background:#fff8e1;color:#f57f17}
                .acordeon-badge--agotado{background:#f5f5f5;color:#616161}
                .acordeon-badge--producto{background:#e3f2fd;color:#1565c0}
                .acordeon-badge--servicio{background:#fce4ec;color:#c62828}
                .acordeon-badge--arriendo{background:#f3e5f5;color:#7b1fa2}
                .acordeon-badge--propiedad{background:#e0f2f1;color:#00695c}
                .acordeon-flecha{transition:transform 0.3s;font-size:1.2rem;color:#999;flex-shrink:0}
                .acordeon-flecha.abierto{transform:rotate(180deg)}
                .acordeon-contenido{max-height:0;overflow:hidden;transition:max-height 0.3s ease-out}
                .acordeon-contenido.abierto{max-height:2000px;transition:max-height 0.5s ease-in}
                .acordeon-detalle{padding:20px;background:#fafafa;border-radius:8px;margin:0 16px 16px}
                .acordeon-detalle__layout{display:flex;gap:20px}
                .acordeon-detalle__img-wrap{position:relative;flex-shrink:0}
                .acordeon-detalle__imagen{width:250px;height:250px;border-radius:12px;object-fit:cover}
                .acordeon-detalle__placeholder{width:250px;height:250px;border-radius:12px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:3.5rem}
                .acordeon-detalle__logo{width:30px;height:30px;border-radius:50%;position:absolute;bottom:8px;right:8px;border:2px solid white;object-fit:cover}
                .acordeon-detalle__overlay{position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(198,40,40,0.7);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.5rem;font-weight:700}
                .acordeon-detalle__info{flex:1;min-width:0}
                .acordeon-detalle__badges{display:flex;gap:6px;margin-bottom:8px;flex-wrap:wrap}
                .acordeon-detalle__nombre{font-size:1.3rem;font-weight:700;margin:4px 0}
                .acordeon-detalle__desc{font-size:0.9rem;color:#666;margin:4px 0;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
                .acordeon-detalle__precio{font-size:1.4rem;font-weight:700;color:#4caf50;margin:8px 0}
                .acordeon-detalle__meta{font-size:0.85rem;color:#666;margin:4px 0}
                .acordeon-detalle__vistas{font-size:0.8rem;color:#999;margin:4px 0}
                .acordeon-desc-expandible{margin-top:12px}
                .acordeon-desc-toggle{color:#4caf50;cursor:pointer;font-size:0.85rem;display:inline-flex;align-items:center;gap:4px;background:none;border:none;font-weight:600;font-family:inherit;padding:0}
                .acordeon-desc-texto{max-height:0;overflow:hidden;transition:max-height 0.3s;font-size:0.9rem;color:#555;line-height:1.6;margin-top:8px}
                .acordeon-desc-texto.abierto{max-height:500px}
                .acordeon-acciones{margin-top:16px}
                .acordeon-btn-wa{display:block;width:100%;padding:12px;background:#25D366;color:white;border:none;border-radius:8px;font-size:1rem;font-weight:600;cursor:pointer;text-align:center;text-decoration:none;margin-bottom:10px;font-family:inherit}
                .acordeon-btn-wa:hover{background:#1da851}
                .acordeon-share-row{display:flex;gap:8px;justify-content:center}
                .acordeon-share-btn{width:40px;height:40px;border-radius:50%;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1rem;transition:transform 0.2s;text-decoration:none}
                .acordeon-share-btn:hover{transform:scale(1.1)}
                .acordeon-share-btn--fb{background:#1877F2;color:white}
                .acordeon-share-btn--tw{background:#000;color:white}
                .acordeon-share-btn--wa{background:#25D366;color:white}
                .acordeon-share-btn--copy{background:#f0f0f0;color:#666}
                .acordeon-img2{width:150px;height:150px;border-radius:8px;object-fit:cover;margin-top:8px}
                @media(max-width:768px){
                    .acordeon-detalle__layout{flex-direction:column}
                    .acordeon-detalle__imagen,.acordeon-detalle__placeholder{width:100%;height:200px}
                    .acordeon-header{padding:10px 12px}
                    .acordeon-badges{display:none}
                    .acordeon-share-row{flex-wrap:wrap}
                    .acordeon-img2{width:100%;max-width:200px;height:auto}
                }
                </style>
                <?php if (!empty($productos)): ?>
                    <?php
                    $_tipoLabels = [
                        'producto'  => '&#128230; Producto',
                        'servicio'  => '&#128295; Servicio',
                        'arriendo'  => '&#127968; Arriendo',
                        'propiedad' => '&#127969; Propiedad',
                    ];
                    $_estadoLabels = [
                        'disponible' => '&#9989; Disponible',
                        'vendido'    => '&#128308; Vendido',
                        'reservado'  => '&#128993; Reservado',
                        'agotado'    => '&#9899; Agotado',
                    ];
                    $_tipoShort = ['producto'=>'&#128230;','servicio'=>'&#128295;','arriendo'=>'&#127968;','propiedad'=>'&#127969;'];
                    $_estadoShort = ['disponible'=>'&#9989;','vendido'=>'&#128308;','reservado'=>'&#128993;','agotado'=>'&#9899;'];
                    $tiposPresentes = [];
                    foreach ($productos as $_p) {
                        $t = $_p['tipo'] ?? 'producto';
                        $tiposPresentes[$t] = ($tiposPresentes[$t] ?? 0) + 1;
                    }
                    ?>
                    <div class="comercio-section">
                        <div class="catalogo-header">
                            <div class="catalogo-titulo">&#127991; Cat&aacute;logo de <?= e($comercio['nombre']) ?></div>
                            <div class="catalogo-subtitulo">Explora nuestros productos y servicios</div>
                            <?php if (count($productos) > 3 && count($tiposPresentes) > 1): ?>
                            <div class="catalogo-filtros">
                                <button class="catalogo-filtro activo" data-tipo="todos">Todos (<?= count($productos) ?>)</button>
                                <?php foreach ($tiposPresentes as $ft => $fc): ?>
                                    <button class="catalogo-filtro" data-tipo="<?= $ft ?>"><?= ($_tipoLabels[$ft] ?? $ft) ?> (<?= $fc ?>)</button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="catalogo-acordeon">
                            <?php foreach ($productos as $idx => $prod):
                                $prodTipo = $prod['tipo'] ?? 'producto';
                                $prodEstado = $prod['estado'] ?? 'disponible';
                                $prodUrl = url('/comercio/' . $comercio['slug']) . '#producto-' . $prod['id'];
                                $prodPrecioFmt = $prod['precio'] ? '$' . number_format($prod['precio'], 0, '', '.') : '';
                                $esVendido = in_array($prodEstado, ['vendido', 'agotado']);
                                $precioDisplay = $prod['precio'] ? '$ ' . number_format($prod['precio'], 0, '', '.') : 'Consultar precio';
                                if ($prodTipo === 'arriendo' && $prod['precio']) $precioDisplay .= ' /mes';
                                $shareTexto = $prod['nombre'] . ' - ' . $prodPrecioFmt . ' en ' . $comercio['nombre'] . ' | Regalos Purranque';
                                // WhatsApp message by type
                                if ($esVendido) {
                                    $msgWa = 'Hola, vi que "' . $prod['nombre'] . '" fue vendido. ¿Tienes algo similar disponible?';
                                } elseif ($prodTipo === 'servicio') {
                                    $msgWa = 'Hola, vi el servicio "' . $prod['nombre'] . '" en regalospurranque.cl y me interesa. ¿Podemos coordinar?';
                                } elseif ($prodTipo === 'arriendo') {
                                    $msgWa = 'Hola, vi "' . $prod['nombre'] . '"';
                                    if ($prod['precio']) $msgWa .= ' (' . $prodPrecioFmt . '/mes)';
                                    $msgWa .= ' en regalospurranque.cl. ¿Est&aacute; disponible para arriendo?';
                                } elseif ($prodTipo === 'propiedad') {
                                    $msgWa = 'Hola, vi la propiedad "' . $prod['nombre'] . '"';
                                    if ($prod['precio']) $msgWa .= ' (' . $prodPrecioFmt . ')';
                                    $msgWa .= ' en regalospurranque.cl. ¿Podemos agendar una visita?';
                                } else {
                                    $msgWa = 'Hola, vi el producto "' . $prod['nombre'] . '"';
                                    if ($prod['precio']) $msgWa .= ' (' . $prodPrecioFmt . ')';
                                    $msgWa .= ' en regalospurranque.cl y me interesa. ¿Est&aacute; disponible?';
                                }
                            ?>
                                <div class="acordeon-item" id="producto-<?= $prod['id'] ?>" data-tipo="<?= $prodTipo ?>" data-estado="<?= $prodEstado ?>">
                                    <div class="acordeon-header" onclick="toggleAcordeon(this)">
                                        <?php if (!empty($prod['imagen'])): ?>
                                            <img class="acordeon-thumb"
                                                 src="<?= asset('img/productos/' . $comercio['id'] . '/thumbs/' . $prod['imagen']) ?>"
                                                 alt="<?= e($prod['nombre']) ?>"
                                                 loading="lazy"
                                                 onerror="this.src='<?= asset('img/productos/' . $comercio['id'] . '/' . $prod['imagen']) ?>'">
                                        <?php else: ?>
                                            <div class="acordeon-thumb-placeholder"><?= mb_substr($prod['nombre'], 0, 1) ?></div>
                                        <?php endif; ?>
                                        <span class="acordeon-nombre"><?= e($prod['nombre']) ?></span>
                                        <span class="acordeon-precio"><?= $precioDisplay ?></span>
                                        <div class="acordeon-badges">
                                            <span class="acordeon-badge acordeon-badge--<?= $prodTipo ?>"><?= ($_tipoShort[$prodTipo] ?? '') ?></span>
                                            <span class="acordeon-badge acordeon-badge--<?= $prodEstado ?>"><?= ($_estadoShort[$prodEstado] ?? '') ?></span>
                                        </div>
                                        <span class="acordeon-flecha">&#9660;</span>
                                    </div>
                                    <div class="acordeon-contenido">
                                        <div class="acordeon-detalle">
                                            <div class="acordeon-detalle__layout">
                                                <div class="acordeon-detalle__img-wrap">
                                                    <?php if (!empty($prod['imagen'])): ?>
                                                        <img class="acordeon-detalle__imagen"
                                                             src="<?= asset('img/productos/' . $comercio['id'] . '/' . $prod['imagen']) ?>"
                                                             alt="<?= e($prod['nombre']) ?>"
                                                             loading="lazy">
                                                    <?php else: ?>
                                                        <div class="acordeon-detalle__placeholder">&#128230;</div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($comercio['logo'])): ?>
                                                        <img class="acordeon-detalle__logo"
                                                             src="<?= asset('img/logos/' . $comercio['logo']) ?>"
                                                             alt="<?= e($comercio['nombre']) ?>" loading="lazy">
                                                    <?php endif; ?>
                                                    <?php if ($esVendido): ?>
                                                        <div class="acordeon-detalle__overlay"><?= strtoupper($prodEstado) ?></div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($prod['imagen2'])): ?>
                                                        <img class="acordeon-img2"
                                                             src="<?= asset('img/productos/' . $comercio['id'] . '/' . $prod['imagen2']) ?>"
                                                             alt="<?= e($prod['nombre']) ?> - imagen 2" loading="lazy">
                                                    <?php endif; ?>
                                                </div>
                                                <div class="acordeon-detalle__info">
                                                    <div class="acordeon-detalle__badges">
                                                        <span class="acordeon-badge acordeon-badge--<?= $prodTipo ?>"><?= ($_tipoLabels[$prodTipo] ?? $prodTipo) ?></span>
                                                        <span class="acordeon-badge acordeon-badge--<?= $prodEstado ?>"><?= ($_estadoLabels[$prodEstado] ?? $prodEstado) ?></span>
                                                    </div>
                                                    <h3 class="acordeon-detalle__nombre"><?= e($prod['nombre']) ?></h3>
                                                    <?php if (!empty($prod['descripcion'])): ?>
                                                        <p class="acordeon-detalle__desc"><?= e($prod['descripcion']) ?></p>
                                                    <?php endif; ?>
                                                    <div class="acordeon-detalle__precio"><?= $precioDisplay ?></div>
                                                    <?php
                                                    $metaParts = [];
                                                    if ($prod['stock'] !== null && $prodTipo === 'producto' && $prod['stock'] > 0) $metaParts[] = '&#128230; ' . $prod['stock'] . ' unidades disponibles';
                                                    if (!empty($prod['condicion'])) $metaParts[] = ucfirst($prod['condicion']);
                                                    ?>
                                                    <?php if (!empty($metaParts)): ?>
                                                        <div class="acordeon-detalle__meta"><?= implode(' | ', $metaParts) ?></div>
                                                    <?php endif; ?>
                                                    <div class="acordeon-detalle__vistas">&#128065; <?= $prod['vistas'] ?? 0 ?> vistas</div>
                                                    <?php if (!empty($prod['descripcion_detallada'])): ?>
                                                        <div class="acordeon-desc-expandible">
                                                            <button class="acordeon-desc-toggle" onclick="event.stopPropagation();toggleDescDetalle(this)">
                                                                &#128221; <span class="acordeon-desc-label">Ver m&aacute;s detalles</span> <span class="acordeon-desc-icono">&#9660;</span>
                                                            </button>
                                                            <div class="acordeon-desc-texto"><?= nl2br(e($prod['descripcion_detallada'])) ?></div>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="acordeon-acciones">
                                                        <?php if (!empty($comercio['whatsapp'])): ?>
                                                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $comercio['whatsapp']) ?>?text=<?= urlencode($msgWa) ?>"
                                                               target="_blank" rel="noopener" class="acordeon-btn-wa"
                                                               onclick="event.stopPropagation();trackWhatsApp(<?= $comercio['id'] ?>)">
                                                                &#128172; <?= $esVendido ? 'Consultar similares' : 'Consultar por WhatsApp' ?>
                                                            </a>
                                                        <?php endif; ?>
                                                        <div class="acordeon-share-row">
                                                            <button class="acordeon-share-btn acordeon-share-btn--fb" title="Facebook"
                                                                    onclick="event.stopPropagation();compartirProd('facebook','<?= e(addslashes($prodUrl)) ?>','<?= e(addslashes($shareTexto)) ?>',<?= $prod['id'] ?>,<?= $comercio['id'] ?>)">&#128266;</button>
                                                            <button class="acordeon-share-btn acordeon-share-btn--tw" title="X / Twitter"
                                                                    onclick="event.stopPropagation();compartirProd('twitter','<?= e(addslashes($prodUrl)) ?>','<?= e(addslashes($shareTexto)) ?>',<?= $prod['id'] ?>,<?= $comercio['id'] ?>)">&#120143;</button>
                                                            <button class="acordeon-share-btn acordeon-share-btn--wa" title="WhatsApp"
                                                                    onclick="event.stopPropagation();compartirProd('whatsapp','<?= e(addslashes($prodUrl)) ?>','<?= e(addslashes($shareTexto)) ?>',<?= $prod['id'] ?>,<?= $comercio['id'] ?>)">&#128172;</button>
                                                            <button class="acordeon-share-btn acordeon-share-btn--copy" title="Copiar enlace"
                                                                    onclick="event.stopPropagation();compartirProd('copiar','<?= e(addslashes($prodUrl)) ?>','',<?= $prod['id'] ?>,<?= $comercio['id'] ?>)">&#128203;</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <script>
                function toggleAcordeon(header){
                    var cont=header.nextElementSibling;
                    var flecha=header.querySelector('.acordeon-flecha');
                    cont.classList.toggle('abierto');
                    flecha.classList.toggle('abierto');
                }
                function toggleDescDetalle(btn){
                    var texto=btn.nextElementSibling;
                    texto.classList.toggle('abierto');
                    var icono=btn.querySelector('.acordeon-desc-icono');
                    var label=btn.querySelector('.acordeon-desc-label');
                    if(texto.classList.contains('abierto')){
                        icono.innerHTML='&#9650;';label.textContent='Ver menos';
                    }else{
                        icono.innerHTML='&#9660;';label.textContent='Ver m\u00e1s detalles';
                    }
                }
                document.querySelectorAll('.catalogo-filtro').forEach(function(btn){
                    btn.addEventListener('click',function(){
                        document.querySelectorAll('.catalogo-filtro').forEach(function(b){b.classList.remove('activo')});
                        btn.classList.add('activo');
                        var tipo=btn.dataset.tipo;
                        document.querySelectorAll('.acordeon-item').forEach(function(item){
                            item.style.display=(tipo==='todos'||item.dataset.tipo===tipo)?'':'none';
                        });
                    });
                });
                function compartirProd(red,url,texto,prodId,comId){
                    var ue=encodeURIComponent(url),te=encodeURIComponent(texto),su='';
                    if(red==='facebook')su='https://www.facebook.com/sharer/sharer.php?u='+ue;
                    else if(red==='twitter')su='https://twitter.com/intent/tweet?text='+te+'&url='+ue;
                    else if(red==='whatsapp')su='https://wa.me/?text='+te+'%20'+ue;
                    else if(red==='copiar'){
                        navigator.clipboard.writeText(url).then(function(){
                            var b=event.target.closest('.acordeon-share-btn');
                            b.innerHTML='&#10003;';setTimeout(function(){b.innerHTML='&#128203;';},2000);
                        });
                    }
                    if(su)window.open(su,'_blank','width=600,height=400');
                    fetch('/api/share-track',{method:'POST',headers:{'Content-Type':'application/json'},
                        body:JSON.stringify({red:red,slug:window.location.pathname.split('/').pop(),tipo:'producto',producto_id:prodId})
                    }).catch(function(){});
                }
                document.addEventListener('DOMContentLoaded',function(){
                    var items=document.querySelectorAll('.acordeon-item');
                    var expandFirst=items.length<=3;
                    if(expandFirst&&items.length>0){
                        var c=items[0].querySelector('.acordeon-contenido');
                        var f=items[0].querySelector('.acordeon-flecha');
                        if(c)c.classList.add('abierto');
                        if(f)f.classList.add('abierto');
                    }
                });
                </script>

                <!-- Galería de fotos -->
                <?php if (!empty($fotos)): ?>
                    <div class="comercio-section">
                        <h2>Galería</h2>
                        <?php if (count($fotos) > 1): ?>
                        <div class="carousel" id="galeriaCarousel">
                            <div class="carousel__track">
                                <?php foreach ($fotos as $foto): ?>
                                <div class="carousel__slide">
                                    <?= picture('img/galeria/' . $foto['ruta'], $foto['titulo'] ?? $comercio['nombre'], 'gallery-img', true, 800, 600) ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel__btn carousel__btn--prev" aria-label="Anterior">&#8249;</button>
                            <button class="carousel__btn carousel__btn--next" aria-label="Siguiente">&#8250;</button>
                            <div class="carousel__dots">
                                <?php foreach ($fotos as $i => $foto): ?>
                                <button class="carousel__dot <?= $i === 0 ? 'carousel__dot--active' : '' ?>"
                                        data-index="<?= $i ?>"
                                        aria-label="Foto <?= $i + 1 ?>"></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="carousel__counter">
                                <span id="carouselCurrent">1</span> / <?= count($fotos) ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="gallery-grid">
                            <?php foreach ($fotos as $foto): ?>
                                <?= picture('img/galeria/' . $foto['ruta'], $foto['titulo'] ?? $comercio['nombre'], 'gallery-img', true, 800, 600) ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Horarios -->
                <?php if (!empty($horarios)): ?>
                    <div class="comercio-section">
                        <h2>Horarios de atención</h2>
                        <table class="horarios-table">
                            <tbody>
                                <?php for ($d = 1; $d <= 6; $d++): ?>
                                    <?php $di = $d % 7; ?>
                                    <tr class="<?= $hoy === $d ? 'horario-hoy' : '' ?>">
                                        <td class="horario-dia"><?= $dias[$d] ?></td>
                                        <td class="horario-hora">
                                            <?php if (isset($horarios[$d]) && $horarios[$d]['cerrado']): ?>
                                                <span class="text-danger">Cerrado</span>
                                            <?php elseif (isset($horarios[$d])): ?>
                                                <?= substr($horarios[$d]['hora_apertura'], 0, 5) ?> - <?= substr($horarios[$d]['hora_cierre'], 0, 5) ?>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endfor; ?>
                                <tr class="<?= $hoy === 0 ? 'horario-hoy' : '' ?>">
                                    <td class="horario-dia"><?= $dias[0] ?></td>
                                    <td class="horario-hora">
                                        <?php if (isset($horarios[0]) && $horarios[0]['cerrado']): ?>
                                            <span class="text-danger">Cerrado</span>
                                        <?php elseif (isset($horarios[0])): ?>
                                            <?= substr($horarios[0]['hora_apertura'], 0, 5) ?> - <?= substr($horarios[0]['hora_cierre'], 0, 5) ?>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <!-- Mapa embebido -->
                <?php if ($comercio['lat'] && $comercio['lng']): ?>
                    <div class="comercio-section">
                        <h2>&#128205; Ubicación</h2>
                        <?php if (!empty($comercio['direccion'])): ?>
                            <p class="text-muted mb-2"><?= e($comercio['direccion']) ?></p>
                        <?php endif; ?>
                        <div id="comercioMap" class="comercio-map"></div>
                        <div class="mt-2">
                            <a href="https://www.google.com/maps/dir/?api=1&destination=<?= $comercio['lat'] ?>,<?= $comercio['lng'] ?>"
                               class="btn btn--outline btn--sm"
                               target="_blank" rel="noopener">
                                &#128663; Cómo llegar
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Contacto + Redes (solo móvil, en desktop va en sidebar) -->
                <div class="only-mobile">
                    <div class="card sidebar-card">
                        <div class="card__body">
                            <h3 class="sidebar-card__title">Información de contacto</h3>

                            <?php if (!empty($comercio['telefono'])): ?>
                                <div class="contact-item">
                                    <span class="contact-item__icon">&#128222;</span>
                                    <a href="tel:<?= e($comercio['telefono']) ?>"><?= e($comercio['telefono']) ?></a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($comercio['whatsapp'])): ?>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $comercio['whatsapp']) ?>"
                                   class="btn btn--secondary btn--block mb-2"
                                   target="_blank"
                                   rel="noopener"
                                   onclick="trackWhatsApp(<?= $comercio['id'] ?>)">
                                    &#128172; WhatsApp
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($comercio['email'])): ?>
                                <a href="#" class="btn btn--outline btn--block mb-2 email-obfuscated" data-e="<?= base64_encode($comercio['email']) ?>" onclick="deobfuscateEmail(this);return false;">
                                    &#9993; Enviar mensaje
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($comercio['sitio_web'])): ?>
                                <a href="<?= e($comercio['sitio_web']) ?>"
                                   class="btn btn--outline btn--block mb-2"
                                   target="_blank"
                                   rel="noopener">
                                    &#127760; Visitar sitio web
                                </a>
                            <?php endif; ?>

                            <button type="button" class="btn btn--outline btn--block mb-2" onclick="copiarEnlacePerfil(this)" style="color:#666;border-color:#ccc">
                                &#128203; Copiar enlace
                            </button>

                            <?php if (!empty($comercio['direccion'])): ?>
                                <div class="contact-item">
                                    <span class="contact-item__icon">&#128205;</span>
                                    <span><?= e($comercio['direccion']) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($comercio['lat'] && $comercio['lng']): ?>
                                <a href="#comercioMap"
                                   class="btn btn--outline btn--block"
                                   onclick="document.getElementById('comercioMap').scrollIntoView({behavior:'smooth'});return false;">
                                    &#128506; Ver en mapa
                                </a>
                            <?php endif; ?>

                            <?php include BASE_PATH . '/views/partials/comercio-redes.php'; ?>
                        </div>
                    </div>
                </div>

                <!-- Ofertas por fechas especiales -->
                <?php if (!empty($comercio['fechas'])): ?>
                    <div class="comercio-section">
                        <h2>Ofertas y Fechas Especiales</h2>
                        <?php foreach ($comercio['fechas'] as $fe): ?>
                            <div class="oferta-card">
                                <a href="<?= url('/fecha/' . $fe['slug']) ?>" class="oferta-card__nombre">
                                    <?= !empty($fe['icono']) ? e($fe['icono']) . ' ' : '' ?><?= e($fe['nombre']) ?>
                                </a>
                                <?php if (!empty($fe['oferta_especial'])): ?>
                                    <p class="oferta-card__detalle"><?= e($fe['oferta_especial']) ?></p>
                                <?php endif; ?>
                                <?php if ($fe['precio_desde'] || $fe['precio_hasta']): ?>
                                    <p class="oferta-card__precio">
                                        <?php if ($fe['precio_desde'] && $fe['precio_hasta']): ?>
                                            Desde $<?= number_format($fe['precio_desde'], 0, ',', '.') ?>
                                            hasta $<?= number_format($fe['precio_hasta'], 0, ',', '.') ?>
                                        <?php elseif ($fe['precio_desde']): ?>
                                            Desde $<?= number_format($fe['precio_desde'], 0, ',', '.') ?>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Reseñas -->
                <div class="comercio-section" id="resenas">
                    <h2>Reseñas<?= $comercio['total_resenas'] ? ' (' . $comercio['total_resenas'] . ')' : '' ?></h2>

                    <?php if (!empty($resenas)): ?>
                        <?php $totalResenas = array_sum($distribucion); ?>
                        <?php if ($totalResenas > 0): ?>
                            <div class="rating-distribution mb-3">
                                <?php foreach ($distribucion as $estrella => $count): ?>
                                    <div class="rating-bar">
                                        <span class="rating-bar__label"><?= $estrella ?>&#9733;</span>
                                        <div class="rating-bar__track">
                                            <div class="rating-bar__fill" style="width: <?= round($count / $totalResenas * 100) ?>%"></div>
                                        </div>
                                        <span class="rating-bar__count"><?= $count ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($resenas as $r): ?>
                            <div class="resena-card">
                                <div class="resena-card__header">
                                    <strong><?= e($r['nombre_autor']) ?></strong>
                                    <span class="resena-card__stars">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <span class="star <?= $i <= $r['calificación'] ? 'star--filled' : '' ?>">&#9733;</span>
                                        <?php endfor; ?>
                                    </span>
                                    <span class="text-muted text-sm"><?= fecha_es($r['created_at']) ?></span>
                                </div>
                                <?php if (!empty($r['tipo_experiencia'])): ?>
                                    <span class="badge badge--primary mb-1"><?= e($r['tipo_experiencia']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($r['comentario'])): ?>
                                    <p class="resena-card__comentario"><?= nl2br(e($r['comentario'])) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($r['respuesta_comercio'])): ?>
                                    <div class="resena-card__respuesta">
                                        <strong>Respuesta del comercio:</strong>
                                        <p><?= nl2br(e($r['respuesta_comercio'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Este comercio aún no tiene reseñas. ¡Sé el primero!</p>
                    <?php endif; ?>

                    <!-- Formulario de nueva resena -->
                    <div class="review-form-container" id="reviewForm">
                        <h3>Deja tu reseña</h3>
                        <form id="newReviewForm" class="review-form" novalidate>
                            <input type="hidden" name="_csrf" value="<?= $csrf ?>">
                            <input type="hidden" name="comercio_id" value="<?= $comercio['id'] ?>">

                            <div class="form-group">
                                <label class="form-label">Calificación *</label>
                                <div class="star-input" id="starInput">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <button type="button" class="star-input__star" data-value="<?= $i ?>" aria-label="<?= $i ?> estrella<?= $i > 1 ? 's' : '' ?>">&#9733;</button>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="calificación" id="calificaciónInput" value="">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="reviewTipo">Tipo de experiencia</label>
                                <select name="tipo_experiencia" id="reviewTipo" class="form-control">
                                    <option value="">Selecciona una opción</option>
                                    <option value="Compra en tienda">&#128717; Compra en tienda</option>
                                    <option value="Compra online">&#128187; Compra online</option>
                                    <option value="Servicio a domicilio">&#128666; Servicio a domicilio</option>
                                    <option value="Consulta o cotización">&#128172; Consulta o cotización</option>
                                    <option value="Visita al local">&#127978; Visita al local</option>
                                    <option value="Regalo recibido">&#127873; Regalo recibido</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="reviewNombre">Nombre *</label>
                                <input type="text" name="nombre" id="reviewNombre" class="form-control"
                                       placeholder="Tu nombre" required minlength="2" maxlength="100">
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="reviewEmail">Correo electrónico *</label>
                                <input type="email" name="email" id="reviewEmail" class="form-control"
                                       placeholder="tu@email.com" required>
                                <small class="text-muted">No será publicado</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="reviewComentario">Comentario *</label>
                                <textarea name="comentario" id="reviewComentario" class="form-control"
                                          placeholder="Cuéntanos tu experiencia con este comercio..." required
                                          minlength="10" maxlength="1000" rows="4"></textarea>
                                <div class="review-char-counter">
                                    <span id="charCount">0</span> / 1000 caracteres
                                </div>
                            </div>

                            <?= \App\Services\Captcha::widget() ?>
                            <button type="submit" class="btn btn--primary" id="submitReview">Enviar reseña</button>
                            <div id="reviewMessage" class="review-message" style="display:none"></div>
                        </form>
                    </div>
                </div>

                <!-- Compartir: below_content -->
                <div class="comercio-section">
                    <?php
                    $sharePageType = 'comercio';
                    $sharePosition = 'below_content';
                    $shareTitle = $comercio['nombre'];
                    $shareDescription = $comercio['descripcion'] ?? '';
                    $shareUrl = url('/comercio/' . $comercio['slug']);
                    $shareSlug = $comercio['slug'];
                    include BASE_PATH . '/views/partials/share-buttons.php';
                    ?>
                </div>

                <!-- Comercios relacionados -->
                <?php if (!empty($relacionados)): ?>
                    <div class="comercio-section">
                        <h2>Comercios similares</h2>
                        <div class="grid grid--auto">
                            <?php foreach ($relacionados as $rel): ?>
                                <a href="<?= url('/comercio/' . $rel['slug']) ?>" class="card">
                                    <?php if (!empty($rel['portada'])): ?>
                                        <?= picture('img/portadas/' . $rel['portada'], $rel['nombre'], 'card__img', true, 400, 267) ?>
                                    <?php else: ?>
                                        <div class="card__img card__img--placeholder">
                                            <?= mb_substr($rel['nombre'], 0, 1) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card__body">
                                        <h3 class="card__title"><?= e($rel['nombre']) ?></h3>
                                        <?php if (!empty($rel['categorias_nombres'])): ?>
                                            <p class="card__text card__text--small"><?= e($rel['categorias_nombres']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Sidebar -->
            <aside class="comercio-sidebar">

                <!-- Contacto (solo desktop, en móvil va en main column) -->
                <div class="only-desktop">
                    <div class="card sidebar-card">
                        <div class="card__body">
                            <h3 class="sidebar-card__title">Información de contacto</h3>

                            <?php if (!empty($comercio['telefono'])): ?>
                                <div class="contact-item">
                                    <span class="contact-item__icon">&#128222;</span>
                                    <a href="tel:<?= e($comercio['telefono']) ?>"><?= e($comercio['telefono']) ?></a>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($comercio['whatsapp'])): ?>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $comercio['whatsapp']) ?>"
                                   class="btn btn--secondary btn--block mb-2"
                                   target="_blank"
                                   rel="noopener"
                                   onclick="trackWhatsApp(<?= $comercio['id'] ?>)">
                                    &#128172; WhatsApp
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($comercio['email'])): ?>
                                <a href="#" class="btn btn--outline btn--block mb-2 email-obfuscated" data-e="<?= base64_encode($comercio['email']) ?>" onclick="deobfuscateEmail(this);return false;">
                                    &#9993; Enviar mensaje
                                </a>
                            <?php endif; ?>

                            <?php if (!empty($comercio['sitio_web'])): ?>
                                <a href="<?= e($comercio['sitio_web']) ?>"
                                   class="btn btn--outline btn--block mb-2"
                                   target="_blank"
                                   rel="noopener">
                                    &#127760; Visitar sitio web
                                </a>
                            <?php endif; ?>

                            <button type="button" class="btn btn--outline btn--block mb-2" onclick="copiarEnlacePerfil(this)" style="color:#666;border-color:#ccc">
                                &#128203; Copiar enlace
                            </button>

                            <?php if (!empty($comercio['direccion'])): ?>
                                <div class="contact-item">
                                    <span class="contact-item__icon">&#128205;</span>
                                    <span><?= e($comercio['direccion']) ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($comercio['lat'] && $comercio['lng']): ?>
                                <a href="#comercioMap"
                                   class="btn btn--outline btn--block"
                                   onclick="document.getElementById('comercioMap').scrollIntoView({behavior:'smooth'});return false;">
                                    &#128506; Ver en mapa
                                </a>
                            <?php endif; ?>

                            <?php // Redes sociales del comercio ?>
                            <?php include BASE_PATH . '/views/partials/comercio-redes.php'; ?>
                        </div>
                    </div>
                </div>

                <?php // Profiles: sidebar position ?>
                <?php $position = 'sidebar'; include BASE_PATH . '/views/partials/social-profiles.php'; ?>

                <?php // Share: sidebar position ?>
                <?php
                $sharePosition = 'sidebar';
                include BASE_PATH . '/views/partials/share-buttons.php';
                ?>

                <!-- Banners sidebar -->
                <?php foreach ($banners as $banner): ?>
                    <div class="sidebar-banner" data-banner-id="<?= $banner['id'] ?>">
                        <a href="<?= e($banner['url']) ?>" target="_blank" rel="noopener" onclick="trackBanner(<?= $banner['id'] ?>)">
                            <?= picture('img/banners/' . $banner['imagen'], $banner['titulo'] ?? 'Publicidad', '', true, 340, 300) ?>
                        </a>
                    </div>
                <?php endforeach; ?>

            </aside>
        </div>
    </div>
</section>

<!-- Leaflet CSS y JS para mapa embebido (self-hosted) -->
<?php if ($comercio['lat'] && $comercio['lng']): ?>
<link rel="stylesheet" href="<?= asset('vendor/leaflet/leaflet.css') ?>">
<script src="<?= asset('vendor/leaflet/leaflet.js') ?>"></script>
<?php endif; ?>

<script>
function deobfuscateEmail(el) {
    var email = atob(el.dataset.e);
    el.href = 'mailto:' + email;
    el.textContent = email;
    el.onclick = null;
    el.classList.remove('email-obfuscated');
}
function trackWhatsApp(comercioId) {
    fetch('<?= url('/api/track') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({tipo: 'whatsapp', comercio_id: comercioId})
    });
}
function trackBanner(bannerId) {
    fetch('<?= url('/api/banner-track') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({banner_id: bannerId, tipo: 'click'})
    });
}

/* Mapa embebido con Leaflet */
<?php if ($comercio['lat'] && $comercio['lng']): ?>
(function() {
    var mapEl = document.getElementById('comercioMap');
    if (!mapEl || typeof L === 'undefined') return;

    var lat = <?= (float)$comercio['lat'] ?>;
    var lng = <?= (float)$comercio['lng'] ?>;
    var map = L.map('comercioMap').setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    var giftIcon = L.divIcon({
        className: 'gift-marker',
        html: '<div class="gift-pin"><span class="gift-pin__emoji">🎁</span><div class="gift-pin__arrow"></div></div>',
        iconSize: [40, 48],
        iconAnchor: [20, 48],
        popupAnchor: [0, -44]
    });

    var marker = L.marker([lat, lng], {icon: giftIcon}).addTo(map);
    <?php
    $popupHtml = '<strong>' . e($comercio['nombre']) . '</strong>';
    if (!empty($comercio['direccion'])) {
        $popupHtml .= '<br>' . e($comercio['direccion']);
    }
    ?>
    marker.bindPopup(<?= json_encode($popupHtml, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>).openPopup();

    setTimeout(function() { map.invalidateSize(); }, 300);
    window.addEventListener('load', function() { map.invalidateSize(); });
    window.addEventListener('resize', function() { map.invalidateSize(); });
})();
<?php endif; ?>

/* Formulario de reseña */
(function() {
    var form = document.getElementById('newReviewForm');
    if (!form) return;

    var starInput = document.getElementById('starInput');
    var calificaciónInput = document.getElementById('calificaciónInput');
    var stars = starInput.querySelectorAll('.star-input__star');

    var comentarioEl = document.getElementById('reviewComentario');
    var charCountEl = document.getElementById('charCount');
    if (comentarioEl && charCountEl) {
        comentarioEl.addEventListener('input', function() {
            var len = this.value.length;
            charCountEl.textContent = len;
            charCountEl.parentElement.classList.toggle('review-char-counter--warn', len > 900);
            charCountEl.parentElement.classList.toggle('review-char-counter--danger', len >= 1000);
        });
    }

    stars.forEach(function(star) {
        star.addEventListener('click', function() {
            var val = parseInt(this.dataset.value);
            calificaciónInput.value = val;
            stars.forEach(function(s) {
                s.classList.toggle('star-input__star--active', parseInt(s.dataset.value) <= val);
            });
        });
        star.addEventListener('mouseenter', function() {
            var val = parseInt(this.dataset.value);
            stars.forEach(function(s) {
                s.classList.toggle('star-input__star--hover', parseInt(s.dataset.value) <= val);
            });
        });
        star.addEventListener('mouseleave', function() {
            stars.forEach(function(s) {
                s.classList.remove('star-input__star--hover');
            });
        });
    });

    function submitReview() {
        var msgEl = document.getElementById('reviewMessage');
        var btn = document.getElementById('submitReview');

        btn.disabled = true;
        btn.textContent = 'Enviando...';

        var data = {
            _csrf: form.querySelector('[name="_csrf"]').value,
            comercio_id: form.querySelector('[name="comercio_id"]').value,
            nombre: form.querySelector('[name="nombre"]').value.trim(),
            email: form.querySelector('[name="email"]').value.trim(),
            calificación: parseInt(calificaciónInput.value),
            tipo_experiencia: form.querySelector('[name="tipo_experiencia"]').value,
            comentario: form.querySelector('[name="comentario"]').value.trim()
        };
        var turnstileInput = form.querySelector('[name="cf-turnstile-response"]');
        if (turnstileInput) data['cf-turnstile-response'] = turnstileInput.value;

        fetch('<?= url('/api/reviews/create') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        })
        .then(function(res) { return res.json(); })
        .then(function(result) {
            if (result.success) {
                showMsg(msgEl, result.message, 'success');
                form.reset();
                calificaciónInput.value = '';
                stars.forEach(function(s) { s.classList.remove('star-input__star--active'); });
                if (charCountEl) charCountEl.textContent = '0';
                if (typeof turnstile !== 'undefined') turnstile.reset();
            } else {
                var errMsg = result.error || 'Error al enviar';
                if (result.errors) {
                    var first = Object.values(result.errors)[0];
                    if (Array.isArray(first)) errMsg = first[0];
                }
                showMsg(msgEl, errMsg, 'error');
            }
            btn.disabled = false;
            btn.textContent = 'Enviar reseña';
        })
        .catch(function() {
            showMsg(msgEl, 'Error de conexión. Intenta nuevamente.', 'error');
            btn.disabled = false;
            btn.textContent = 'Enviar reseña';
        });
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var msgEl = document.getElementById('reviewMessage');

        if (!calificaciónInput.value) {
            showMsg(msgEl, 'Selecciona una calificación', 'error');
            return;
        }

        submitReview();
    });

    function showMsg(el, msg, type) {
        el.textContent = msg;
        el.className = 'review-message review-message--' + type;
        el.style.display = 'block';
        setTimeout(function() { el.style.display = 'none'; }, 6000);
    }
})();

/* Lightbox galeria */
(function() {
    var imgs = document.querySelectorAll('.gallery-img');
    if (!imgs.length) return;

    var overlay = document.createElement('div');
    overlay.className = 'lightbox';
    overlay.innerHTML = '<button class="lightbox__close" aria-label="Cerrar">&times;</button>' +
        '<button class="lightbox__prev" aria-label="Anterior">&#8249;</button>' +
        '<img class="lightbox__img" src="" alt="" width="1200" height="800">' +
        '<button class="lightbox__next" aria-label="Siguiente">&#8250;</button>';
    document.body.appendChild(overlay);

    var lbImg = overlay.querySelector('.lightbox__img');
    var currentIndex = 0;

    function open(index) {
        currentIndex = index;
        lbImg.src = imgs[index].src;
        lbImg.alt = imgs[index].alt;
        overlay.classList.add('lightbox--active');
        document.body.style.overflow = 'hidden';
    }

    function close() {
        overlay.classList.remove('lightbox--active');
        document.body.style.overflow = '';
    }

    function nav(dir) {
        currentIndex = ((currentIndex + dir) % imgs.length + imgs.length) % imgs.length;
        lbImg.src = imgs[currentIndex].src;
        lbImg.alt = imgs[currentIndex].alt;
    }

    imgs.forEach(function(img, i) {
        img.addEventListener('click', function() { open(i); });
    });

    overlay.querySelector('.lightbox__close').addEventListener('click', close);
    overlay.querySelector('.lightbox__prev').addEventListener('click', function() { nav(-1); });
    overlay.querySelector('.lightbox__next').addEventListener('click', function() { nav(1); });
    overlay.addEventListener('click', function(e) { if (e.target === overlay) close(); });

    document.addEventListener('keydown', function(e) {
        if (!overlay.classList.contains('lightbox--active')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowLeft') nav(-1);
        if (e.key === 'ArrowRight') nav(1);
    });
})();

/* Carrusel de galeria */
(function() {
    var carousel = document.getElementById('galeriaCarousel');
    if (!carousel) return;

    var track = carousel.querySelector('.carousel__track');
    var slides = carousel.querySelectorAll('.carousel__slide');
    var prevBtn = carousel.querySelector('.carousel__btn--prev');
    var nextBtn = carousel.querySelector('.carousel__btn--next');
    var dots = carousel.querySelectorAll('.carousel__dot');
    var counterEl = document.getElementById('carouselCurrent');
    var total = slides.length;
    var current = 0;
    var startX = 0;
    var isDragging = false;

    function goTo(index) {
        current = ((index % total) + total) % total;
        track.style.transform = 'translateX(-' + (current * 100) + '%)';
        dots.forEach(function(d, i) {
            d.classList.toggle('carousel__dot--active', i === current);
        });
        if (counterEl) counterEl.textContent = current + 1;
    }

    prevBtn.addEventListener('click', function() { goTo(current - 1); });
    nextBtn.addEventListener('click', function() { goTo(current + 1); });

    dots.forEach(function(dot) {
        dot.addEventListener('click', function() {
            goTo(parseInt(this.dataset.index));
        });
    });

    track.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        isDragging = true;
    }, { passive: true });

    track.addEventListener('touchend', function(e) {
        if (!isDragging) return;
        isDragging = false;
        var diff = startX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) goTo(current + 1);
            else goTo(current - 1);
        }
    }, { passive: true });

    carousel.setAttribute('tabindex', '0');
    carousel.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft') { goTo(current - 1); e.preventDefault(); }
        if (e.key === 'ArrowRight') { goTo(current + 1); e.preventDefault(); }
    });
})();

/* Copiar enlace del perfil */
function copiarEnlacePerfil(btn) {
    var url = <?= json_encode(url('/comercio/' . $comercio['slug']), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    var original = btn.innerHTML;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function() {
            btn.innerHTML = '&#10003; ¡Enlace copiado!';
            btn.style.color = '#2e7d32';
            btn.style.borderColor = '#2e7d32';
            setTimeout(function() { btn.innerHTML = original; btn.style.color = '#666'; btn.style.borderColor = '#ccc'; }, 2000);
        });
    } else {
        var ta = document.createElement('textarea');
        ta.value = url;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        document.execCommand('copy');
        document.body.removeChild(ta);
        btn.innerHTML = '&#10003; ¡Enlace copiado!';
        btn.style.color = '#2e7d32';
        btn.style.borderColor = '#2e7d32';
        setTimeout(function() { btn.innerHTML = original; btn.style.color = '#666'; btn.style.borderColor = '#ccc'; }, 2000);
    }
}
</script>
