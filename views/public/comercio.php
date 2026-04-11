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

                <!-- Catalogo de productos -->
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
                .acordeon-thumb-ph{width:60px;height:60px;border-radius:50%;background:#e0e0e0;display:flex;align-items:center;justify-content:center;font-size:1.2rem;color:#999;margin-right:14px;flex-shrink:0;font-weight:700}
                .acordeon-nombre{flex:1;font-weight:600;font-size:1rem;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
                .acordeon-precio{color:#4caf50;font-weight:700;margin-right:12px;font-size:1rem;white-space:nowrap}
                .acordeon-badges{display:flex;gap:6px;margin-right:12px}
                .acordeon-badge{font-size:0.7rem;padding:2px 8px;border-radius:12px;white-space:nowrap}
                .acordeon-badge--disponible{background:#e8f5e9;color:#2e7d32}.acordeon-badge--vendido{background:#ffebee;color:#c62828}
                .acordeon-badge--reservado{background:#fff8e1;color:#f57f17}.acordeon-badge--agotado{background:#f5f5f5;color:#616161}
                .acordeon-badge--producto{background:#e3f2fd;color:#1565c0}.acordeon-badge--servicio{background:#fce4ec;color:#c62828}
                .acordeon-badge--inmueble{background:#e0f2f1;color:#00695c}
                .acordeon-flecha{transition:transform 0.3s;flex-shrink:0;line-height:0}
                .acordeon-flecha.abierto{transform:rotate(180deg)}
                .acordeon-contenido{max-height:0;overflow:hidden;transition:max-height 0.3s ease-out}
                .acordeon-contenido.abierto{max-height:3000px;transition:max-height 0.5s ease-in}
                .acordeon-detalle{padding:20px;background:#fafafa;border-radius:8px;margin:0 16px 16px}
                .acordeon-det-layout{display:flex;gap:20px}
                .acordeon-det-imgwrap{position:relative;flex-shrink:0}
                .acordeon-det-img{width:250px;height:250px;border-radius:12px;object-fit:cover}
                .acordeon-det-ph{width:250px;height:250px;border-radius:12px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:3.5rem}
                .acordeon-det-logo{width:30px;height:30px;border-radius:50%;position:absolute;bottom:8px;right:8px;border:2px solid white;object-fit:cover}
                .acordeon-det-overlay{position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(198,40,40,0.7);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.5rem;font-weight:700}
                .acordeon-det-info{flex:1;min-width:0}
                .acordeon-det-badges{display:flex;gap:6px;margin-bottom:8px;flex-wrap:wrap}
                .acordeon-det-nombre{font-size:1.3rem;font-weight:700;margin:4px 0}
                .acordeon-det-desc{font-size:0.9rem;color:#666;margin:4px 0}
                .acordeon-det-precio{font-size:1.4rem;font-weight:700;color:#4caf50;margin:8px 0}
                .acordeon-det-meta{font-size:0.85rem;color:#666;margin:4px 0}
                .acordeon-det-vistas{font-size:0.8rem;color:#999;margin:4px 0}
                .acordeon-det-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px 16px;font-size:0.88rem;color:#555;margin:10px 0}
                .acordeon-det-grid span{display:flex;align-items:center;gap:4px}
                .acordeon-amenidades{display:flex;flex-wrap:wrap;gap:6px;margin:8px 0}
                .acordeon-amenidad{font-size:0.75rem;padding:3px 10px;border-radius:14px;background:#e8f5e9;color:#2e7d32;font-weight:500}
                .acordeon-desc-exp{margin-top:12px}
                .acordeon-desc-tog{color:#4caf50;cursor:pointer;font-size:0.85rem;display:inline-flex;align-items:center;gap:4px;background:none;border:none;font-weight:600;font-family:inherit;padding:0}
                .acordeon-desc-txt{max-height:0;overflow:hidden;transition:max-height 0.3s;font-size:0.9rem;color:#555;line-height:1.6;margin-top:8px}
                .acordeon-desc-txt.abierto{max-height:500px}
                .acordeon-acciones{margin-top:16px}
                .acordeon-btn-wa{display:block;width:100%;padding:12px;background:#25D366;color:white;border:none;border-radius:8px;font-size:1rem;font-weight:600;cursor:pointer;text-align:center;text-decoration:none;margin-bottom:10px;font-family:inherit}
                .acordeon-btn-wa:hover{background:#1da851}
                .acordeon-share-row{display:flex;gap:8px;justify-content:center}
                .acordeon-share-btn{width:40px;height:40px;border-radius:50%;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;text-decoration:none}
                .acordeon-share-btn:hover{transform:scale(1.15);box-shadow:0 2px 8px rgba(0,0,0,0.15)}
                .acordeon-share-btn--fb{background:#1877F2}.acordeon-share-btn--tw{background:#000}
                .acordeon-share-btn--wa{background:#25D366}.acordeon-share-btn--copy{background:#f0f0f0}
                .acordeon-img2{width:150px;height:150px;border-radius:8px;object-fit:cover;margin-top:8px}
                .producto-galeria{position:relative}
                .producto-galeria__principal-container{position:relative;overflow:hidden;border-radius:12px;cursor:zoom-in;width:300px;height:300px}
                .producto-galeria__principal-img{width:100%;height:100%;object-fit:cover;transform-origin:center center;transition:transform 0.1s}
                .producto-galeria__principal-container:hover .producto-galeria__principal-img{transform:scale(2)}
                .producto-galeria__logo{width:30px;height:30px;border-radius:50%;position:absolute;bottom:8px;right:8px;border:2px solid white;object-fit:cover;z-index:2}
                .producto-galeria__overlay{position:absolute;top:0;left:0;right:0;bottom:0;background:rgba(198,40,40,0.7);border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;font-size:1.5rem;font-weight:700;z-index:1}
                .producto-galeria__placeholder{width:300px;height:300px;border-radius:12px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:3.5rem}
                .producto-galeria__thumbs{display:flex;gap:6px;margin-top:8px;overflow-x:auto}
                .producto-galeria__thumb{width:60px;height:60px;border-radius:6px;object-fit:cover;cursor:pointer;border:2px solid transparent;transition:border-color 0.2s;flex-shrink:0}
                .producto-galeria__thumb.activa{border-color:#4caf50}
                .producto-galeria__thumb:hover{border-color:#999}
                @media(max-width:768px){
                    .acordeon-det-layout{flex-direction:column}
                    .acordeon-det-img,.acordeon-det-ph{width:100%;height:200px}
                    .acordeon-header{padding:10px 12px}.acordeon-badges{display:none}
                    .acordeon-share-row{flex-wrap:wrap}.acordeon-det-grid{grid-template-columns:1fr}
                    .acordeon-img2{width:100%;max-width:200px;height:auto}
                    .producto-galeria__principal-container{width:100%;height:200px;cursor:default}
                    .producto-galeria__principal-container:hover .producto-galeria__principal-img{transform:none}
                    .producto-galeria__placeholder{width:100%;height:200px}
                }
                </style>
                <?php if (!empty($productos)): ?>
                    <?php
                    $_tL=['producto'=>'&#128230; Producto','servicio'=>'&#128295; Servicio','inmueble'=>'&#127968; Inmueble'];
                    $_eL=['disponible'=>'&#9989; Disponible','vendido'=>'&#128308; Vendido','reservado'=>'&#128993; Reservado','agotado'=>'&#9899; Agotado'];
                    $_tS=['producto'=>'&#128230;','servicio'=>'&#128295;','inmueble'=>'&#127968;'];
                    $_eS=['disponible'=>'&#9989;','vendido'=>'&#128308;','reservado'=>'&#128993;','agotado'=>'&#9899;'];
                    $tiposP=[];
                    foreach($productos as $_p){$t=$_p['tipo']??'producto';$tiposP[$t]=($tiposP[$t]??0)+1;}
                    ?>
                    <div class="comercio-section">
                        <div class="catalogo-header">
                            <div class="catalogo-titulo">&#127991; Cat&aacute;logo de <?= e($comercio['nombre']) ?></div>
                            <div class="catalogo-subtitulo">Explora nuestros productos y servicios</div>
                            <?php if(count($productos)>3 && count($tiposP)>1): ?>
                            <div class="catalogo-filtros">
                                <button class="catalogo-filtro activo" data-tipo="todos">Todos (<?= count($productos) ?>)</button>
                                <?php foreach($tiposP as $ft=>$fc): ?>
                                <button class="catalogo-filtro" data-tipo="<?= $ft ?>"><?= ($_tL[$ft]??$ft) ?> (<?= $fc ?>)</button>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="catalogo-acordeon">
                        <?php
                    // Load fotos for all products
                    $_fotosByProduct = [];
                    foreach ($productos as $_fp) {
                        $_fotosByProduct[$_fp['id']] = \App\Models\ProductoFoto::findByProductoId($_fp['id']);
                    }
                    ?>
                        <?php foreach($productos as $idx=>$prod):
                            $prodFotos = $_fotosByProduct[$prod['id']] ?? [];
                            $pT=$prod['tipo']??'producto';
                            $pE=$prod['estado']??'disponible';
                            $pUrl=url('/producto/'.$prod['id']);
                            $pPF=$prod['precio']?'$'.number_format($prod['precio'],0,'','.'):'';
                            $esV=in_array($pE,['vendido','agotado']);
                            $pD=$prod['precio']?'$ '.number_format($prod['precio'],0,',','.'):'Consultar precio';
                            $pOp=$prod['operacion']??'';
                            if($pT==='inmueble'&&$pOp==='arriendo'&&$prod['precio'])$pD.=' /mes';
                            if($pT==='inmueble'&&$pOp==='permuta')$pD='Permuta';
                            $sTxt=$prod['nombre'].' - '.$pPF.' en '.$comercio['nombre'].' | Regalos Purranque';
                            if($esV){$mW='Hola, vi que "'.$prod['nombre'].'" fue vendido. Tienes algo similar?';}
                            elseif($pT==='servicio'){$mW='Hola, vi el servicio "'.$prod['nombre'].'" en regalospurranque.cl y me interesa. Podemos coordinar?';}
                            elseif($pT==='inmueble'&&$pOp==='arriendo'){$mW='Hola, vi "'.$prod['nombre'].'"';if($prod['precio'])$mW.=' ('.$pPF.'/mes)';$mW.=' en regalospurranque.cl. Esta disponible para arriendo?';}
                            elseif($pT==='inmueble'&&$pOp==='venta'){$mW='Hola, vi "'.$prod['nombre'].'"';if($prod['precio'])$mW.=' ('.$pPF.')';$mW.=' en regalospurranque.cl. Podemos agendar una visita?';}
                            elseif($pT==='inmueble'){$mW='Hola, vi "'.$prod['nombre'].'" en regalospurranque.cl y me interesa. Podemos conversar?';}
                            else{$mW='Hola, vi "'.$prod['nombre'].'"';if($prod['precio'])$mW.=' ('.$pPF.')';$mW.=' en regalospurranque.cl y me interesa. Esta disponible?';}
                        ?>
                            <div class="acordeon-item" id="producto-<?= $prod['id'] ?>" data-tipo="<?= $pT ?>" data-estado="<?= $pE ?>">
                                <div class="acordeon-header" onclick="toggleAc(this)">
                                    <?php if(!empty($prod['imagen'])): ?>
                                    <img class="acordeon-thumb" src="<?= asset('img/productos/'.$comercio['id'].'/thumbs/'.$prod['imagen']) ?>" alt="<?= e($prod['nombre']) ?>" loading="lazy" onerror="this.src='<?= asset('img/productos/'.$comercio['id'].'/'.$prod['imagen']) ?>'">
                                    <?php else: ?>
                                    <div class="acordeon-thumb-ph"><?= mb_substr($prod['nombre'],0,1) ?></div>
                                    <?php endif; ?>
                                    <span class="acordeon-nombre"><?= e($prod['nombre']) ?></span>
                                    <span class="acordeon-precio"><?= $pD ?></span>
                                    <div class="acordeon-badges">
                                        <span class="acordeon-badge acordeon-badge--<?= $pT ?>"><?= ($_tS[$pT]??'') ?></span>
                                        <span class="acordeon-badge acordeon-badge--<?= $pE ?>"><?= ($_eS[$pE]??'') ?></span>
                                    </div>
                                    <svg class="acordeon-flecha" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#999" stroke-width="2" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
                                </div>
                                <div class="acordeon-contenido">
                                    <div class="acordeon-detalle">
                                        <div class="acordeon-det-layout">
                                            <div class="producto-galeria" data-pid="<?= $prod['id'] ?>">
                                                <?php
                                                $fotoPrincipal = !empty($prodFotos) ? $prodFotos[0] : null;
                                                $fotoSrc = $fotoPrincipal ? asset('img/productos/'.$comercio['id'].'/'.$fotoPrincipal['imagen']) : '';
                                                ?>
                                                <?php if($fotoPrincipal): ?>
                                                <div class="producto-galeria__principal-container">
                                                    <img class="producto-galeria__principal-img" src="<?= $fotoSrc ?>" alt="<?= e($prod['nombre']) ?>" loading="lazy">
                                                    <?php if(!empty($comercio['logo'])): ?>
                                                    <img class="producto-galeria__logo" src="<?= asset('img/logos/'.$comercio['logo']) ?>" alt="<?= e($comercio['nombre']) ?>" loading="lazy">
                                                    <?php endif; ?>
                                                    <?php if($esV): ?><div class="producto-galeria__overlay"><?= strtoupper($pE) ?></div><?php endif; ?>
                                                </div>
                                                <?php else: ?>
                                                <div class="producto-galeria__placeholder">&#128230;</div>
                                                <?php endif; ?>
                                                <?php if(count($prodFotos) > 1): ?>
                                                <div class="producto-galeria__thumbs">
                                                    <?php foreach($prodFotos as $fi => $pf): ?>
                                                    <img class="producto-galeria__thumb<?= $fi === 0 ? ' activa' : '' ?>"
                                                         src="<?= asset('img/productos/'.$comercio['id'].'/thumbs/'.$pf['imagen']) ?>"
                                                         data-full="<?= asset('img/productos/'.$comercio['id'].'/'.$pf['imagen']) ?>"
                                                         alt="Foto <?= $fi+1 ?>"
                                                         loading="lazy"
                                                         onclick="cambiarFotoPrincipal(this)">
                                                    <?php endforeach; ?>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="acordeon-det-info">
                                                <div class="acordeon-det-badges">
                                                    <span class="acordeon-badge acordeon-badge--<?= $pT ?>"><?= ($_tL[$pT]??$pT) ?></span>
                                                    <span class="acordeon-badge acordeon-badge--<?= $pE ?>"><?= ($_eL[$pE]??$pE) ?></span>
                                                </div>
                                                <h3 class="acordeon-det-nombre"><?= e($prod['nombre']) ?></h3>
                                                <?php if(!empty($prod['descripcion'])): ?>
                                                <p class="acordeon-det-desc"><?= e($prod['descripcion']) ?></p>
                                                <?php endif; ?>
                                                <div class="acordeon-det-precio"><?= $pD ?></div>

                                                <?php if($pT==='servicio'): ?>
                                                    <?php if(!empty($prod['modalidad'])): ?>
                                                    <div class="acordeon-det-meta">&#128205; Modalidad: <?= ucfirst($prod['modalidad']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if(!empty($prod['horario_atencion'])): ?>
                                                    <div class="acordeon-det-meta">&#128336; <?= e($prod['horario_atencion']) ?></div>
                                                    <?php endif; ?>

                                                <?php elseif($pT==='inmueble'): ?>
                                                    <?php if(!empty($prod['tipo_propiedad'])): ?>
                                                    <div class="acordeon-det-meta" style="font-weight:600;margin-bottom:6px"><?= ucfirst(str_replace('_',' ',$prod['tipo_propiedad'])) ?><?php if($pOp==='venta') echo ' en venta'; elseif($pOp==='arriendo') echo ' en arriendo'; elseif($pOp==='permuta') echo ' en permuta'; ?></div>
                                                    <?php endif; ?>
                                                    <div class="acordeon-det-grid">
                                                        <?php if($prod['dormitorios']!==null): ?><span>&#128716; <?= $prod['dormitorios'] ?> dormitorios</span><?php endif; ?>
                                                        <?php if($prod['banos']!==null): ?><span>&#128703; <?= $prod['banos'] ?> ba&ntilde;os</span><?php endif; ?>
                                                        <?php if($prod['estacionamientos']): ?><span>&#128663; <?= $prod['estacionamientos'] ?> estac.</span><?php endif; ?>
                                                        <?php if($prod['bodegas']): ?><span>&#128230; <?= $prod['bodegas'] ?> bodegas</span><?php endif; ?>
                                                        <?php if($prod['superficie_terreno']): ?><span>&#128208; Terreno: <?= number_format($prod['superficie_terreno'],0,',','.') ?> m&sup2;</span><?php endif; ?>
                                                        <?php if($prod['superficie_construida']): ?><span>&#127959; Construido: <?= number_format($prod['superficie_construida'],0,',','.') ?> m&sup2;</span><?php endif; ?>
                                                        <?php if(!empty($prod['direccion_propiedad'])): ?><span>&#128205; <?= e($prod['direccion_propiedad']) ?></span><?php endif; ?>
                                                        <?php if(!empty($prod['comuna_propiedad'])): ?><span>&#127968; <?= e($prod['comuna_propiedad']) ?></span><?php endif; ?>
                                                        <?php if(!empty($prod['disponible_desde'])): ?><span>&#128197; Desde: <?= date('d/m/Y',strtotime($prod['disponible_desde'])) ?></span><?php endif; ?>
                                                        <?php if($prod['ano_construccion']): ?><span>&#128197; A&ntilde;o: <?= $prod['ano_construccion'] ?></span><?php endif; ?>
                                                        <?php if($prod['gastos_comunes']): ?><span>&#128176; GC: $ <?= number_format($prod['gastos_comunes'],0,',','.') ?></span><?php endif; ?>
                                                    </div>
                                                    <?php
                                                    $amen=[];
                                                    if(!empty($prod['amoblado']))$amen[]='&#129681; Amoblado';
                                                    if(!empty($prod['acepta_mascotas']))$amen[]='&#128062; Mascotas OK';
                                                    if(!empty($prod['tiene_lenera']))$amen[]='&#129717; Le&ntilde;era';
                                                    if(!empty($prod['tiene_areas_verdes']))$amen[]='&#127807; &Aacute;reas verdes';
                                                    if(!empty($prod['tiene_calefaccion']))$amen[]='&#128293; Calefacci&oacute;n'.(!empty($prod['tipo_calefaccion'])?' ('.e($prod['tipo_calefaccion']).')':'');
                                                    if(!empty($prod['agua_potable']))$amen[]='&#128167; Agua potable';
                                                    if(!empty($prod['alcantarillado']))$amen[]='&#128688; Alcantarillado';
                                                    if(!empty($prod['luz_electrica']))$amen[]='&#128161; Luz';
                                                    if(isset($prod['es_rural']))$amen[]=$prod['es_rural']?'&#127806; Rural':'&#127961; Urbano';
                                                    ?>
                                                    <?php if(!empty($amen)): ?>
                                                    <div class="acordeon-amenidades">
                                                        <?php foreach($amen as $a): ?><span class="acordeon-amenidad"><?= $a ?></span><?php endforeach; ?>
                                                    </div>
                                                    <?php endif; ?>

                                                <?php else: ?>
                                                    <?php
                                                    $mp=[];
                                                    if($prod['stock']!==null&&$prod['stock']>0)$mp[]='&#128230; '.$prod['stock'].' unidades';
                                                    if(!empty($prod['condicion']))$mp[]=ucfirst($prod['condicion']);
                                                    ?>
                                                    <?php if(!empty($mp)): ?>
                                                    <div class="acordeon-det-meta"><?= implode(' | ',$mp) ?></div>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <div class="acordeon-det-vistas">&#128065; <?= $prod['vistas']??0 ?> vistas</div>

                                                <?php if(!empty($prod['descripcion_detallada'])): ?>
                                                <div class="acordeon-desc-exp">
                                                    <button class="acordeon-desc-tog" onclick="event.stopPropagation();toggleDet(this)">&#128221; <span class="dtl">Ver m&aacute;s detalles</span> <span class="dti">&#9660;</span></button>
                                                    <div class="acordeon-desc-txt"><?= nl2br(e($prod['descripcion_detallada'])) ?></div>
                                                </div>
                                                <?php endif; ?>

                                                <div class="acordeon-acciones">
                                                    <?php if(!empty($comercio['whatsapp'])): ?>
                                                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/','', $comercio['whatsapp']) ?>?text=<?= urlencode($mW) ?>" target="_blank" rel="noopener" class="acordeon-btn-wa" onclick="event.stopPropagation();trackWhatsApp(<?= $comercio['id'] ?>)">&#128172; <?= $esV?'Consultar similares':(($pT==='inmueble'&&$pOp==='arriendo')?'Consultar por arriendo':(($pT==='inmueble'&&$pOp==='venta')?'Agendar visita':(($pT==='inmueble')?'Consultar por inmueble':($pT==='servicio'?'Consultar por servicio':'Consultar por WhatsApp')))) ?></a>
                                                    <?php endif; ?>
                                                    <div class="acordeon-share-row">
                                                        <button class="acordeon-share-btn acordeon-share-btn--fb" title="Facebook" onclick="event.stopPropagation();shProd('facebook','<?= e(addslashes($pUrl)) ?>','<?= e(addslashes($sTxt)) ?>',<?= $prod['id'] ?>,<?= $comercio['id'] ?>)"><svg width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></button>
                                                        <button class="acordeon-share-btn acordeon-share-btn--tw" title="X / Twitter" onclick="event.stopPropagation();shProd('twitter','<?= e(addslashes($pUrl)) ?>','<?= e(addslashes($sTxt)) ?>',<?= $prod['id'] ?>,<?= $comercio['id'] ?>)"><svg width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></button>
                                                        <button class="acordeon-share-btn acordeon-share-btn--wa" title="WhatsApp" onclick="event.stopPropagation();shProd('whatsapp','<?= e(addslashes($pUrl)) ?>','<?= e(addslashes($sTxt)) ?>',<?= $prod['id'] ?>,<?= $comercio['id'] ?>)"><svg width="18" height="18" viewBox="0 0 24 24" fill="white"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></button>
                                                        <button class="acordeon-share-btn acordeon-share-btn--copy" title="Copiar enlace" onclick="event.stopPropagation();shProd('copiar','<?= e(addslashes($pUrl)) ?>','',<?= $prod['id'] ?>,<?= $comercio['id'] ?>)"><svg width="18" height="18" viewBox="0 0 24 24" fill="#666"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg></button>
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
                function toggleAc(h){var c=h.nextElementSibling,f=h.querySelector('.acordeon-flecha');c.classList.toggle('abierto');f.classList.toggle('abierto')}
                function toggleDet(b){var t=b.nextElementSibling;t.classList.toggle('abierto');b.querySelector('.dti').innerHTML=t.classList.contains('abierto')?'&#9650;':'&#9660;';b.querySelector('.dtl').textContent=t.classList.contains('abierto')?'Ver menos':'Ver m\u00e1s detalles'}
                document.querySelectorAll('.catalogo-filtro').forEach(function(b){b.addEventListener('click',function(){document.querySelectorAll('.catalogo-filtro').forEach(function(x){x.classList.remove('activo')});b.classList.add('activo');var t=b.dataset.tipo;document.querySelectorAll('.acordeon-item').forEach(function(i){i.style.display=(t==='todos'||i.dataset.tipo===t)?'':'none'})})});
                function shProd(r,u,t,pId,cId){var ue=encodeURIComponent(u),te=encodeURIComponent(t),s='';if(r==='facebook')s='https://www.facebook.com/sharer/sharer.php?u='+ue;else if(r==='twitter')s='https://twitter.com/intent/tweet?text='+te+'&url='+ue;else if(r==='whatsapp')s='https://wa.me/?text='+te+'%20'+ue;else if(r==='copiar'){navigator.clipboard.writeText(u).then(function(){var b=event.target.closest('.acordeon-share-btn');var o=b.innerHTML;b.innerHTML='<svg width="18" height="18" viewBox="0 0 24 24" fill="#4caf50"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>';setTimeout(function(){b.innerHTML=o},2000)});return}if(s)window.open(s,'_blank','width=600,height=400');fetch('/api/share-track',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({red:r,tipo:'producto',producto_id:pId,comercio_id:cId,url:'/producto/'+pId})}).catch(function(){})}
                // Gallery: zoom follow mouse
                document.querySelectorAll('.producto-galeria__principal-container').forEach(function(cont){
                    var img=cont.querySelector('.producto-galeria__principal-img');
                    cont.addEventListener('mousemove',function(e){
                        var r=cont.getBoundingClientRect();
                        var x=((e.clientX-r.left)/r.width)*100;
                        var y=((e.clientY-r.top)/r.height)*100;
                        img.style.transformOrigin=x+'% '+y+'%';
                    });
                    cont.addEventListener('mouseleave',function(){img.style.transformOrigin='center center'});
                });
                // Gallery: change principal on thumb click
                function cambiarFotoPrincipal(thumb){
                    var g=thumb.closest('.producto-galeria');
                    var p=g.querySelector('.producto-galeria__principal-img');
                    if(p)p.src=thumb.dataset.full;
                    g.querySelectorAll('.producto-galeria__thumb').forEach(function(t){t.classList.remove('activa')});
                    thumb.classList.add('activa');
                }
                document.addEventListener('DOMContentLoaded',function(){var it=document.querySelectorAll('.acordeon-item');if(it.length>0&&it.length<=3){var c=it[0].querySelector('.acordeon-contenido'),f=it[0].querySelector('.acordeon-flecha');if(c)c.classList.add('abierto');if(f)f.classList.add('abierto')}});
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
