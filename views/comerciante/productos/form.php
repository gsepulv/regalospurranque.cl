<?php
/**
 * Formulario crear/editar producto - contextual por tipo
 * Variables: $comercio, $producto (null si es nuevo), $errors, $old
 */
$esEdicion = !empty($producto);
$errors = $_SESSION['flash_errors'] ?? [];
$old    = $_SESSION['flash_old'] ?? [];
unset($_SESSION['flash_errors'], $_SESSION['flash_old']);

$nombre              = e($old['nombre'] ?? $producto['nombre'] ?? '');
$descripcion         = e($old['descripcion'] ?? $producto['descripcion'] ?? '');
$descripcion_detallada = e($old['descripcion_detallada'] ?? $producto['descripcion_detallada'] ?? '');
$precio              = $old['precio'] ?? $producto['precio'] ?? '';
$activo              = $old['activo'] ?? $producto['activo'] ?? 1;
$tipo                = $old['tipo'] ?? $producto['tipo'] ?? 'producto';
$estado              = $old['estado'] ?? $producto['estado'] ?? 'disponible';
$stock               = $old['stock'] ?? $producto['stock'] ?? '';
$condicion           = $old['condicion'] ?? $producto['condicion'] ?? 'nuevo';
$modalidad           = $old['modalidad'] ?? $producto['modalidad'] ?? 'presencial';
$horario_atencion    = e($old['horario_atencion'] ?? $producto['horario_atencion'] ?? '');
$tipo_propiedad      = $old['tipo_propiedad'] ?? $producto['tipo_propiedad'] ?? '';
$operacion           = $old['operacion'] ?? $producto['operacion'] ?? '';
$superficie_terreno  = $old['superficie_terreno'] ?? $producto['superficie_terreno'] ?? '';
$superficie_construida = $old['superficie_construida'] ?? $producto['superficie_construida'] ?? '';
$dormitorios         = $old['dormitorios'] ?? $producto['dormitorios'] ?? '';
$banos               = $old['banos'] ?? $producto['banos'] ?? '';
$estacionamientos    = $old['estacionamientos'] ?? $producto['estacionamientos'] ?? '';
$bodegas             = $old['bodegas'] ?? $producto['bodegas'] ?? '';
$direccion_propiedad = e($old['direccion_propiedad'] ?? $producto['direccion_propiedad'] ?? '');
$comuna_propiedad    = e($old['comuna_propiedad'] ?? $producto['comuna_propiedad'] ?? '');
$disponible_desde    = $old['disponible_desde'] ?? $producto['disponible_desde'] ?? '';
$ano_construccion    = $old['ano_construccion'] ?? $producto['ano_construccion'] ?? '';
$amoblado            = $old['amoblado'] ?? $producto['amoblado'] ?? 0;
$acepta_mascotas     = $old['acepta_mascotas'] ?? $producto['acepta_mascotas'] ?? 0;
$tiene_lenera        = $old['tiene_lenera'] ?? $producto['tiene_lenera'] ?? 0;
$tiene_areas_verdes  = $old['tiene_areas_verdes'] ?? $producto['tiene_areas_verdes'] ?? 0;
$tiene_calefaccion   = $old['tiene_calefaccion'] ?? $producto['tiene_calefaccion'] ?? 0;
$tipo_calefaccion    = $old['tipo_calefaccion'] ?? $producto['tipo_calefaccion'] ?? '';
$es_rural            = $old['es_rural'] ?? $producto['es_rural'] ?? 0;
$agua_potable        = $old['agua_potable'] ?? $producto['agua_potable'] ?? 0;
$alcantarillado      = $old['alcantarillado'] ?? $producto['alcantarillado'] ?? 0;
$luz_electrica       = $old['luz_electrica'] ?? $producto['luz_electrica'] ?? 0;
$gastos_comunes      = $old['gastos_comunes'] ?? $producto['gastos_comunes'] ?? '';
?>

<section class="section">
    <div class="container" style="max-width:720px">

        <div style="margin-bottom:1.5rem">
            <h1 style="font-size:1.5rem;margin:0"><?= $esEdicion ? 'Editar producto' : 'Nuevo producto' ?></h1>
            <a href="<?= url('/mi-comercio/productos') ?>" style="color:#6B7280;font-size:0.85rem;text-decoration:none">&larr; Volver a mis productos</a>
            <?php if (isset($totalProductos, $maxProductos)):
                $_restantes = $maxProductos - $totalProductos;
                $_color = $_restantes <= 0 ? '#dc2626' : ($_restantes === 1 ? '#d97706' : '#6B7280');
            ?>
                <p style="font-size:0.85rem;margin:0.5rem 0 0;color:<?= $_color ?>">&#128230; <?= $totalProductos ?> de <?= $maxProductos ?> productos utilizados (Plan: <?= e($plan['nombre'] ?? 'Freemium') ?>)</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($errors)): ?>
            <div style="background:#FEE2E2;border:1px solid #FECACA;color:#991B1B;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;font-size:0.9rem">
                <ul style="margin:0;padding-left:1.25rem">
                    <?php foreach ($errors as $err): ?>
                        <li><?= e($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST"
              action="<?= url($esEdicion ? '/mi-comercio/productos/actualizar/' . $producto['id'] : '/mi-comercio/productos/guardar') ?>"
              enctype="multipart/form-data"
              style="background:var(--color-white);border-radius:12px;padding:1.5rem;box-shadow:0 2px 8px rgba(0,0,0,0.08)">

            <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
            <input type="hidden" name="tipo" id="tipoInput" value="<?= e($tipo) ?>">

            <!-- ═══════════ Selector de tipo ═══════════ -->
            <div style="margin-bottom:1.5rem">
                <label style="display:block;font-weight:600;margin-bottom:0.5rem;font-size:0.9rem">&#127991; Tipo de publicaci&#243;n *</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem" id="tipoCards">
                    <div class="tipo-card" data-tipo="producto"
                         style="border:2px solid #E5E7EB;border-radius:10px;padding:1rem;text-align:center;cursor:pointer;transition:all 0.2s">
                        <div style="font-size:1.75rem">&#128230;</div>
                        <div style="font-weight:600;font-size:0.9rem;margin-top:0.25rem">Producto</div>
                        <div style="font-size:0.75rem;color:#9CA3AF">Art&#237;culos f&#237;sicos</div>
                    </div>
                    <div class="tipo-card" data-tipo="servicio"
                         style="border:2px solid #E5E7EB;border-radius:10px;padding:1rem;text-align:center;cursor:pointer;transition:all 0.2s">
                        <div style="font-size:1.75rem">&#128736;</div>
                        <div style="font-weight:600;font-size:0.9rem;margin-top:0.25rem">Servicio</div>
                        <div style="font-size:0.75rem;color:#9CA3AF">Servicios profesionales</div>
                    </div>
                    <div class="tipo-card" data-tipo="inmueble"
                         style="border:2px solid #E5E7EB;border-radius:10px;padding:1rem;text-align:center;cursor:pointer;transition:all 0.2s">
                        <div style="font-size:1.75rem">&#127968;</div>
                        <div style="font-weight:600;font-size:0.9rem;margin-top:0.25rem">Inmueble</div>
                        <div style="font-size:0.75rem;color:#9CA3AF">Arriendo o venta</div>
                    </div>
                    <div class="tipo-card" data-tipo="propiedad"
                         style="border:2px solid #E5E7EB;border-radius:10px;padding:1rem;text-align:center;cursor:pointer;transition:all 0.2s">
                        <div style="font-size:1.75rem">&#127969;</div>
                        <div style="font-weight:600;font-size:0.9rem;margin-top:0.25rem">Propiedad</div>
                        <div style="font-size:0.75rem;color:#9CA3AF">Casa, terreno, parcela</div>
                    </div>
                </div>
            </div>

            <!-- ═══════════ Campos comunes ═══════════ -->
            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodNombre">Nombre *</label>
                <input type="text" name="nombre" id="prodNombre" value="<?= $nombre ?>" required maxlength="150"
                       class="form-control" placeholder="Ej: Ramo de rosas rojas">
            </div>

            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodDescripcion">Descripci&#243;n breve</label>
                <textarea name="descripcion" id="prodDescripcion" maxlength="500" rows="3" class="form-control"
                          placeholder="Descripci&#243;n corta (se muestra en listados)"><?= $descripcion ?></textarea>
                <div style="text-align:right;font-size:0.75rem;color:#9CA3AF;margin-top:0.25rem">
                    <span id="descCount">0</span> / 500
                </div>
            </div>

            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodDescDetallada">Descripci&#243;n detallada</label>
                <textarea name="descripcion_detallada" id="prodDescDetallada" maxlength="2000" rows="5" class="form-control"
                          placeholder="Descripci&#243;n completa con todos los detalles (se muestra en la ficha)"><?= $descripcion_detallada ?></textarea>
                <div style="text-align:right;font-size:0.75rem;color:#9CA3AF;margin-top:0.25rem">
                    <span id="descDetCount">0</span> / 2000
                </div>
            </div>

            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodPrecio" id="labelPrecio">Precio (CLP)</label>
                <input type="number" name="precio" id="prodPrecio" value="<?= $precio ?>" min="0" step="1"
                       class="form-control" placeholder="Ej: 24990">
                <small style="color:#9CA3AF;font-size:0.75rem">D&#233;jalo en blanco si prefieres no mostrar precio</small>
            </div>

            <!-- ═══════════ Detalles del producto ═══════════ -->
            <div id="secProducto" style="margin-bottom:1rem;padding:1rem;background:#F9FAFB;border-radius:8px;border:1px solid #E5E7EB">
                <h3 style="font-size:1rem;margin:0 0 1rem;color:#374151">&#128230; Detalles del producto</h3>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodStock">Stock disponible</label>
                    <input type="number" name="stock" id="prodStock" value="<?= $stock ?>" min="0" step="1"
                           class="form-control" placeholder="Ej: 10" style="max-width:200px">
                    <small style="color:#9CA3AF;font-size:0.75rem">Opcional. D&#233;jalo vac&#237;o si no manejas inventario.</small>
                </div>

                <div style="margin-bottom:0">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Condici&#243;n</label>
                    <div style="display:flex;gap:1rem;flex-wrap:wrap">
                        <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                            <input type="radio" name="condicion" value="nuevo" <?= $condicion === 'nuevo' ? 'checked' : '' ?>> Nuevo
                        </label>
                        <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                            <input type="radio" name="condicion" value="usado" <?= $condicion === 'usado' ? 'checked' : '' ?>> Usado
                        </label>
                        <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                            <input type="radio" name="condicion" value="reacondicionado" <?= $condicion === 'reacondicionado' ? 'checked' : '' ?>> Reacondicionado
                        </label>
                    </div>
                </div>
            </div>

            <!-- ═══════════ Detalles del servicio ═══════════ -->
            <div id="secServicio" style="margin-bottom:1rem;padding:1rem;background:#F9FAFB;border-radius:8px;border:1px solid #E5E7EB;display:none">
                <h3 style="font-size:1rem;margin:0 0 1rem;color:#374151">&#128736; Detalles del servicio</h3>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Modalidad</label>
                    <div style="display:flex;gap:1rem;flex-wrap:wrap">
                        <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                            <input type="radio" name="modalidad" value="presencial" <?= $modalidad === 'presencial' ? 'checked' : '' ?>> Presencial
                        </label>
                        <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                            <input type="radio" name="modalidad" value="domicilio" <?= $modalidad === 'domicilio' ? 'checked' : '' ?>> A domicilio
                        </label>
                        <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                            <input type="radio" name="modalidad" value="online" <?= $modalidad === 'online' ? 'checked' : '' ?>> Online
                        </label>
                        <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                            <input type="radio" name="modalidad" value="mixto" <?= $modalidad === 'mixto' ? 'checked' : '' ?>> Mixto
                        </label>
                    </div>
                </div>

                <div style="margin-bottom:0">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodHorario">Horario de atenci&#243;n</label>
                    <input type="text" name="horario_atencion" id="prodHorario" value="<?= $horario_atencion ?>"
                           class="form-control" placeholder="Ej: Lunes a viernes 9:00 - 18:00" maxlength="200">
                </div>
            </div>

            <!-- ═══════════ Detalles del inmueble ═══════════ -->
            <div id="secInmueble" style="margin-bottom:1rem;padding:1rem;background:#F9FAFB;border-radius:8px;border:1px solid #E5E7EB;display:none">
                <h3 style="font-size:1rem;margin:0 0 1rem;color:#374151">&#127968; Detalles del inmueble</h3>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodTipoPropiedad">Tipo de propiedad *</label>
                    <select name="tipo_propiedad" id="prodTipoPropiedad" class="form-control">
                        <option value="">-- Seleccionar --</option>
                        <option value="casa" <?= $tipo_propiedad === 'casa' ? 'selected' : '' ?>>Casa</option>
                        <option value="departamento" <?= $tipo_propiedad === 'departamento' ? 'selected' : '' ?>>Departamento</option>
                        <option value="terreno" <?= $tipo_propiedad === 'terreno' ? 'selected' : '' ?>>Terreno</option>
                        <option value="parcela" <?= $tipo_propiedad === 'parcela' ? 'selected' : '' ?>>Parcela</option>
                        <option value="local_comercial" <?= $tipo_propiedad === 'local_comercial' ? 'selected' : '' ?>>Local comercial</option>
                        <option value="oficina" <?= $tipo_propiedad === 'oficina' ? 'selected' : '' ?>>Oficina</option>
                        <option value="bodega" <?= $tipo_propiedad === 'bodega' ? 'selected' : '' ?>>Bodega</option>
                        <option value="estacionamiento" <?= $tipo_propiedad === 'estacionamiento' ? 'selected' : '' ?>>Estacionamiento</option>
                        <option value="cabana" <?= $tipo_propiedad === 'cabana' ? 'selected' : '' ?>>Caba&#241;a</option>
                        <option value="habitacion" <?= $tipo_propiedad === 'habitacion' ? 'selected' : '' ?>>Habitaci&#243;n</option>
                    </select>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Operaci&#243;n</label>
                    <div style="display:flex;gap:1rem;flex-wrap:wrap">
                        <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                            <input type="radio" name="operacion" value="arriendo" <?= $operacion === 'arriendo' ? 'checked' : '' ?>> Arriendo
                        </label>
                        <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                            <input type="radio" name="operacion" value="venta" <?= $operacion === 'venta' ? 'checked' : '' ?>> Venta
                        </label>
                        <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                            <input type="radio" name="operacion" value="arriendo_temporal" <?= $operacion === 'arriendo_temporal' ? 'checked' : '' ?>> Arriendo temporal
                        </label>
                    </div>
                </div>

                <div style="margin-bottom:1rem">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Zona</label>
                    <div style="display:flex;gap:1rem">
                        <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                            <input type="radio" name="es_rural" value="0" <?= !$es_rural ? 'checked' : '' ?>> Urbano
                        </label>
                        <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                            <input type="radio" name="es_rural" value="1" <?= $es_rural ? 'checked' : '' ?>> Rural
                        </label>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1rem">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodDireccion">Direcci&#243;n</label>
                        <input type="text" name="direccion_propiedad" id="prodDireccion" value="<?= $direccion_propiedad ?>"
                               class="form-control" placeholder="Ej: Av. Principal 123" maxlength="255">
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodComuna">Comuna</label>
                        <input type="text" name="comuna_propiedad" id="prodComuna" value="<?= $comuna_propiedad ?>"
                               class="form-control" placeholder="Ej: Purranque" maxlength="100">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:0.75rem;margin-bottom:1rem">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.85rem" for="prodDorm">Dormitorios</label>
                        <input type="number" name="dormitorios" id="prodDorm" value="<?= $dormitorios ?>" min="0"
                               class="form-control" placeholder="0">
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.85rem" for="prodBanos">Ba&#241;os</label>
                        <input type="number" name="banos" id="prodBanos" value="<?= $banos ?>" min="0"
                               class="form-control" placeholder="0">
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.85rem" for="prodEstac">Estac.</label>
                        <input type="number" name="estacionamientos" id="prodEstac" value="<?= $estacionamientos ?>" min="0"
                               class="form-control" placeholder="0">
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.85rem" for="prodBodegas">Bodegas</label>
                        <input type="number" name="bodegas" id="prodBodegas" value="<?= $bodegas ?>" min="0"
                               class="form-control" placeholder="0">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1rem">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodSupTerreno">Superficie terreno (m&#178;)</label>
                        <input type="number" name="superficie_terreno" id="prodSupTerreno" value="<?= $superficie_terreno ?>" min="0" step="0.01"
                               class="form-control" placeholder="Ej: 500">
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodSupConst">Superficie construida (m&#178;)</label>
                        <input type="number" name="superficie_construida" id="prodSupConst" value="<?= $superficie_construida ?>" min="0" step="0.01"
                               class="form-control" placeholder="Ej: 120">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1rem">
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodDisponible">Disponible desde</label>
                        <input type="date" name="disponible_desde" id="prodDisponible" value="<?= $disponible_desde ?>"
                               class="form-control">
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodGastos">Gastos comunes (CLP)</label>
                        <input type="number" name="gastos_comunes" id="prodGastos" value="<?= $gastos_comunes ?>" min="0" step="1"
                               class="form-control" placeholder="Ej: 50000">
                    </div>
                </div>

                <div id="wrapAnoConstruccion" style="margin-bottom:0">
                    <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodAnoConstruccion">A&#241;o de construcci&#243;n</label>
                    <input type="number" name="ano_construccion" id="prodAnoConstruccion" value="<?= $ano_construccion ?>"
                           min="1900" max="2030" class="form-control" placeholder="Ej: 2015" style="max-width:200px">
                </div>
            </div>

            <!-- ═══════════ Amenidades ═══════════ -->
            <div id="secAmenidades" style="margin-bottom:1rem;padding:1rem;background:#F9FAFB;border-radius:8px;border:1px solid #E5E7EB;display:none">
                <h3 style="font-size:1rem;margin:0 0 1rem;color:#374151">&#9989; Amenidades y servicios b&#225;sicos</h3>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem 1rem">
                    <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                        <input type="checkbox" name="amoblado" value="1" <?= $amoblado ? 'checked' : '' ?>> Amoblado
                    </label>
                    <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                        <input type="checkbox" name="acepta_mascotas" value="1" <?= $acepta_mascotas ? 'checked' : '' ?>> Acepta mascotas
                    </label>
                    <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                        <input type="checkbox" name="tiene_lenera" value="1" <?= $tiene_lenera ? 'checked' : '' ?>> Le&#241;era
                    </label>
                    <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                        <input type="checkbox" name="tiene_areas_verdes" value="1" <?= $tiene_areas_verdes ? 'checked' : '' ?>> &#193;reas verdes
                    </label>
                    <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                        <input type="checkbox" name="tiene_calefaccion" value="1" id="chkCalefaccion" <?= $tiene_calefaccion ? 'checked' : '' ?>> Calefacci&#243;n
                    </label>
                    <div id="wrapTipoCalefaccion" style="<?= $tiene_calefaccion ? '' : 'display:none' ?>">
                        <select name="tipo_calefaccion" id="selTipoCalefaccion" class="form-control" style="font-size:0.85rem">
                            <option value="">Tipo...</option>
                            <option value="lena" <?= $tipo_calefaccion === 'lena' ? 'selected' : '' ?>>Le&#241;a</option>
                            <option value="pellet" <?= $tipo_calefaccion === 'pellet' ? 'selected' : '' ?>>Pellet</option>
                            <option value="gas" <?= $tipo_calefaccion === 'gas' ? 'selected' : '' ?>>Gas</option>
                            <option value="electrica" <?= $tipo_calefaccion === 'electrica' ? 'selected' : '' ?>>El&#233;ctrica</option>
                            <option value="central" <?= $tipo_calefaccion === 'central' ? 'selected' : '' ?>>Central</option>
                            <option value="kerosene" <?= $tipo_calefaccion === 'kerosene' ? 'selected' : '' ?>>Kerosene</option>
                            <option value="otra" <?= $tipo_calefaccion === 'otra' ? 'selected' : '' ?>>Otra</option>
                        </select>
                    </div>
                    <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                        <input type="checkbox" name="agua_potable" value="1" <?= $agua_potable ? 'checked' : '' ?>> Agua potable
                    </label>
                    <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                        <input type="checkbox" name="alcantarillado" value="1" <?= $alcantarillado ? 'checked' : '' ?>> Alcantarillado
                    </label>
                    <label style="display:flex;align-items:center;gap:0.35rem;cursor:pointer;font-size:0.9rem">
                        <input type="checkbox" name="luz_electrica" value="1" <?= $luz_electrica ? 'checked' : '' ?>> Luz el&#233;ctrica
                    </label>
                </div>
            </div>

            <!-- ═══════════ Estado ═══════════ -->
            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem" for="prodEstado">Estado</label>
                <select name="estado" id="prodEstado" class="form-control">
                    <option value="disponible" <?= $estado === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                    <option value="agotado" <?= $estado === 'agotado' ? 'selected' : '' ?>>Agotado</option>
                    <option value="reservado" <?= $estado === 'reservado' ? 'selected' : '' ?>>Reservado</option>
                    <option value="pausado" <?= $estado === 'pausado' ? 'selected' : '' ?>>Pausado</option>
                </select>
            </div>

            <!-- ═══════════ Imagen principal ═══════════ -->
            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Imagen principal</label>
                <?php if ($esEdicion && !empty($producto['imagen'])): ?>
                    <div style="margin-bottom:0.5rem">
                        <img src="<?= asset('img/productos/' . $comercio['id'] . '/' . $producto['imagen']) ?>"
                             alt="<?= e($producto['nombre']) ?>"
                             style="max-width:200px;max-height:200px;border-radius:8px;object-fit:cover">
                        <p style="font-size:0.75rem;color:#9CA3AF;margin:0.25rem 0 0">Imagen actual. Sube otra para reemplazarla.</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" id="prodImagen">
                <small style="color:#9CA3AF;font-size:0.75rem">JPG, PNG o WebP. M&#225;ximo 2 MB.</small>
                <div id="imgPreview" style="margin-top:0.5rem;display:none">
                    <img id="previewImg" src="" alt="Preview" style="max-width:200px;max-height:200px;border-radius:8px;object-fit:cover">
                </div>
            </div>

            <!-- ═══════════ Imagen secundaria ═══════════ -->
            <div style="margin-bottom:1rem">
                <label style="display:block;font-weight:600;margin-bottom:0.35rem;font-size:0.9rem">Imagen secundaria</label>
                <?php if ($esEdicion && !empty($producto['imagen2'])): ?>
                    <div style="margin-bottom:0.5rem">
                        <img src="<?= asset('img/productos/' . $comercio['id'] . '/' . $producto['imagen2']) ?>"
                             alt="<?= e($producto['nombre']) ?> (2)"
                             style="max-width:200px;max-height:200px;border-radius:8px;object-fit:cover">
                        <p style="font-size:0.75rem;color:#9CA3AF;margin:0.25rem 0 0">Imagen secundaria actual. Sube otra para reemplazarla.</p>
                    </div>
                <?php endif; ?>
                <input type="file" name="imagen2" accept="image/jpeg,image/png,image/webp" id="prodImagen2">
                <small style="color:#9CA3AF;font-size:0.75rem">JPG, PNG o WebP. M&#225;ximo 2 MB. (Opcional)</small>
                <div id="imgPreview2" style="margin-top:0.5rem;display:none">
                    <img id="previewImg2" src="" alt="Preview" style="max-width:200px;max-height:200px;border-radius:8px;object-fit:cover">
                </div>
            </div>

            <!-- ═══════════ Activo ═══════════ -->
            <div style="margin-bottom:1.25rem">
                <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-size:0.9rem">
                    <input type="checkbox" name="activo" value="1" <?= $activo ? 'checked' : '' ?>>
                    <span>Producto activo (visible en mi perfil)</span>
                </label>
            </div>

            <!-- ═══════════ Botones ═══════════ -->
            <div style="display:flex;gap:0.75rem">
                <button type="submit" class="btn btn--primary" style="flex:1">
                    <?= $esEdicion ? 'Guardar cambios' : 'Crear producto' ?>
                </button>
                <a href="<?= url('/mi-comercio/productos') ?>" class="btn btn--outline" style="flex:1;text-align:center">
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</section>

<script>
(function() {
    /* ── Tipo selector ── */
    var tipoInput = document.getElementById('tipoInput');
    var cards = document.querySelectorAll('.tipo-card');
    var secProducto = document.getElementById('secProducto');
    var secServicio = document.getElementById('secServicio');
    var secInmueble = document.getElementById('secInmueble');
    var secAmenidades = document.getElementById('secAmenidades');
    var labelPrecio = document.getElementById('labelPrecio');
    var prodNombre = document.getElementById('prodNombre');
    var wrapAnoConstruccion = document.getElementById('wrapAnoConstruccion');

    function toggleTipo(tipo) {
        tipoInput.value = tipo;

        // Reset all sections
        secProducto.style.display = 'none';
        secServicio.style.display = 'none';
        secInmueble.style.display = 'none';
        secAmenidades.style.display = 'none';
        if (wrapAnoConstruccion) wrapAnoConstruccion.style.display = 'none';

        // Update cards visual
        cards.forEach(function(c) {
            if (c.getAttribute('data-tipo') === tipo) {
                c.style.borderColor = '#16A34A';
                c.style.background = '#F0FDF4';
            } else {
                c.style.borderColor = '#E5E7EB';
                c.style.background = '';
            }
        });

        // Show relevant sections and update labels/placeholders
        switch (tipo) {
            case 'producto':
                secProducto.style.display = 'block';
                labelPrecio.textContent = 'Precio (CLP)';
                prodNombre.placeholder = 'Ej: Ramo de rosas rojas';
                break;
            case 'servicio':
                secServicio.style.display = 'block';
                labelPrecio.textContent = 'Precio / Tarifa (CLP)';
                prodNombre.placeholder = 'Ej: Servicio de electricidad';
                break;
            case 'inmueble':
                secInmueble.style.display = 'block';
                secAmenidades.style.display = 'block';
                labelPrecio.textContent = 'Precio arriendo / venta (CLP)';
                prodNombre.placeholder = 'Ej: Departamento 2D 1B centro';
                break;
            case 'propiedad':
                secInmueble.style.display = 'block';
                secAmenidades.style.display = 'block';
                if (wrapAnoConstruccion) wrapAnoConstruccion.style.display = 'block';
                labelPrecio.textContent = 'Precio (CLP)';
                prodNombre.placeholder = 'Ej: Parcela 5000 m\u00B2 camino a Corte Alto';
                break;
        }
    }

    cards.forEach(function(card) {
        card.addEventListener('click', function() {
            toggleTipo(this.getAttribute('data-tipo'));
        });
    });

    // Initial state
    toggleTipo(tipoInput.value || 'producto');

    /* ── Character counters ── */
    var desc = document.getElementById('prodDescripcion');
    var descCounter = document.getElementById('descCount');
    if (desc && descCounter) {
        descCounter.textContent = desc.value.length;
        desc.addEventListener('input', function() { descCounter.textContent = this.value.length; });
    }

    var descDet = document.getElementById('prodDescDetallada');
    var descDetCounter = document.getElementById('descDetCount');
    if (descDet && descDetCounter) {
        descDetCounter.textContent = descDet.value.length;
        descDet.addEventListener('input', function() { descDetCounter.textContent = this.value.length; });
    }

    /* ── Image preview (main) ── */
    function setupImagePreview(inputId, previewWrapId, previewImgId) {
        var fileInput = document.getElementById(inputId);
        var preview = document.getElementById(previewWrapId);
        var previewImg = document.getElementById(previewImgId);
        if (fileInput && preview && previewImg) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    if (this.files[0].size > 2 * 1024 * 1024) {
                        alert('La imagen no puede superar los 2 MB.');
                        this.value = '';
                        preview.style.display = 'none';
                        return;
                    }
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }
    }

    setupImagePreview('prodImagen', 'imgPreview', 'previewImg');
    setupImagePreview('prodImagen2', 'imgPreview2', 'previewImg2');

    /* ── Calefaccion toggle ── */
    var chkCalefaccion = document.getElementById('chkCalefaccion');
    var wrapTipoCal = document.getElementById('wrapTipoCalefaccion');
    if (chkCalefaccion && wrapTipoCal) {
        chkCalefaccion.addEventListener('change', function() {
            wrapTipoCal.style.display = this.checked ? '' : 'none';
            if (!this.checked) {
                document.getElementById('selTipoCalefaccion').value = '';
            }
        });
    }
})();
</script>
