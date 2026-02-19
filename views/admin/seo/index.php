<?php
/**
 * Admin - SEO con 6 tabs (config, metatags, schema, redirects, sitemap, tools)
 * Variables varían según tab activo
 */
$currentTab = $tab ?? 'config';
?>
<div class="admin-breadcrumb">
    <a href="<?= url('/admin/dashboard') ?>">Dashboard</a> &rsaquo;
    <span>SEO</span>
</div>

<h2>Configuraci&oacute;n SEO</h2>

<!-- Tabs -->
<div class="admin-tabs" style="margin-bottom:var(--spacing-6);flex-wrap:wrap">
    <a href="<?= url('/admin/seo?tab=config') ?>"
       class="admin-tab <?= $currentTab === 'config' ? 'admin-tab--active' : '' ?>">
        Config Global
    </a>
    <a href="<?= url('/admin/seo?tab=metatags') ?>"
       class="admin-tab <?= $currentTab === 'metatags' ? 'admin-tab--active' : '' ?>">
        Meta Tags
    </a>
    <a href="<?= url('/admin/seo?tab=schema') ?>"
       class="admin-tab <?= $currentTab === 'schema' ? 'admin-tab--active' : '' ?>">
        Schema.org
    </a>
    <a href="<?= url('/admin/seo?tab=redirects') ?>"
       class="admin-tab <?= $currentTab === 'redirects' ? 'admin-tab--active' : '' ?>">
        Redirecciones
    </a>
    <a href="<?= url('/admin/seo?tab=sitemap') ?>"
       class="admin-tab <?= $currentTab === 'sitemap' ? 'admin-tab--active' : '' ?>">
        Sitemap
    </a>
    <a href="<?= url('/admin/seo?tab=tools') ?>"
       class="admin-tab <?= $currentTab === 'tools' ? 'admin-tab--active' : '' ?>">
        Herramientas
    </a>
</div>

<?php if ($currentTab === 'config'): ?>
    <!-- ═══════ TAB 1: Configuración Global ═══════ -->
    <form method="POST" action="<?= url('/admin/seo/config') ?>">
        <?= csrf_field() ?>

        <div class="admin-card" style="margin-bottom:var(--spacing-6)">
            <div style="padding:var(--spacing-6)">
                <h3 style="margin:0 0 var(--spacing-4)">Meta Tags por Defecto</h3>

                <div class="form-group">
                    <label class="form-label">Sufijo de t&iacute;tulo</label>
                    <input type="text" name="site_title_suffix" class="form-control"
                           value="<?= e($config['site_title_suffix'] ?? '') ?>"
                           placeholder=" &mdash; Regalos Purranque">
                    <small class="form-hint">Se agrega al final de cada t&iacute;tulo de p&aacute;gina.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripci&oacute;n por defecto</label>
                    <textarea name="default_description" class="form-control" rows="3"
                              data-maxlength="160"><?= e($config['default_description'] ?? '') ?></textarea>
                    <small class="form-hint">M&aacute;ximo 160 caracteres.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Keywords por defecto</label>
                    <input type="text" name="default_keywords" class="form-control"
                           value="<?= e($config['default_keywords'] ?? '') ?>"
                           placeholder="purranque, comercios, directorio">
                    <small class="form-hint">Separadas por comas.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">URL base can&oacute;nica</label>
                    <input type="url" name="canonical_base" class="form-control"
                           value="<?= e($config['canonical_base'] ?? '') ?>"
                           placeholder="https://regalos.purranque.info">
                </div>

                <div class="form-group">
                    <label class="form-label">Imagen OG por defecto</label>
                    <input type="text" name="og_default_image" class="form-control"
                           value="<?= e($config['og_default_image'] ?? '') ?>"
                           placeholder="img/og/default.jpg">
                    <small class="form-hint">Ruta relativa a /assets/.</small>
                </div>
            </div>
        </div>

        <div class="admin-card" style="margin-bottom:var(--spacing-6)">
            <div style="padding:var(--spacing-6)">
                <h3 style="margin:0 0 var(--spacing-4)">Herramientas de An&aacute;lisis</h3>

                <div class="form-group">
                    <label class="form-label">Google Analytics (Measurement ID)</label>
                    <input type="text" name="google_analytics" class="form-control"
                           value="<?= e($config['google_analytics'] ?? '') ?>"
                           placeholder="G-XXXXXXXXXX" style="width:280px">
                </div>

                <div class="form-group">
                    <label class="form-label">Google Search Console (verificaci&oacute;n)</label>
                    <input type="text" name="google_search_console" class="form-control"
                           value="<?= e($config['google_search_console'] ?? '') ?>"
                           placeholder="google-site-verification=...">
                </div>
            </div>
        </div>

        <div class="admin-card" style="margin-bottom:var(--spacing-6)">
            <div style="padding:var(--spacing-6)">
                <h3 style="margin:0 0 var(--spacing-4)">Robots.txt Extra</h3>
                <div class="form-group">
                    <textarea name="robots_txt_extra" class="form-control" rows="5"
                              placeholder="Disallow: /admin/&#10;Disallow: /api/"
                              style="font-family:monospace;font-size:0.8rem"><?= e($config['robots_txt_extra'] ?? '') ?></textarea>
                    <small class="form-hint">Directivas adicionales para robots.txt (una por l&iacute;nea).</small>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn--primary">Guardar configuraci&oacute;n SEO</button>
    </form>

<?php elseif ($currentTab === 'metatags'): ?>
    <!-- ═══════ TAB 2: Meta Tags por Página ═══════ -->
    <div class="admin-card" style="margin-bottom:var(--spacing-6)">
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>P&aacute;gina</th>
                        <th>T&iacute;tulo SEO</th>
                        <th>Descripci&oacute;n</th>
                        <th style="width:80px;text-align:center">Score</th>
                        <th style="width:80px">Acci&oacute;n</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pages as $page): ?>
                        <tr>
                            <td>
                                <strong><?= e($page['label']) ?></strong>
                                <br><small style="color:var(--color-gray)"><code><?= e($page['url']) ?></code></small>
                            </td>
                            <td>
                                <small><?= $page['title'] ? e(truncate($page['title'], 50)) : '<span style="color:var(--color-gray)">Sin t&iacute;tulo</span>' ?></small>
                            </td>
                            <td>
                                <small><?= $page['description'] ? e(truncate($page['description'], 60)) : '<span style="color:var(--color-gray)">Sin descripci&oacute;n</span>' ?></small>
                            </td>
                            <td style="text-align:center">
                                <?php
                                $scoreColor = '#dc2626';
                                if ($page['score'] >= 80) $scoreColor = '#059669';
                                elseif ($page['score'] >= 50) $scoreColor = '#d97706';
                                ?>
                                <div style="position:relative;display:inline-block;width:40px;height:40px">
                                    <svg viewBox="0 0 36 36" style="width:40px;height:40px;transform:rotate(-90deg)">
                                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                                        <circle cx="18" cy="18" r="15.9" fill="none" stroke="<?= $scoreColor ?>"
                                                stroke-width="3" stroke-dasharray="<?= $page['score'] ?>, 100"
                                                stroke-linecap="round"/>
                                    </svg>
                                    <span style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:0.7rem;font-weight:700;color:<?= $scoreColor ?>"><?= $page['score'] ?></span>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn--outline btn--sm"
                                        onclick="openMetaEditor('<?= e($page['key']) ?>', '<?= e(addslashes($page['label'])) ?>', '<?= e(addslashes($page['title'])) ?>', '<?= e(addslashes($page['description'])) ?>', '<?= e(addslashes($page['keywords'])) ?>', '<?= e(addslashes($page['image'])) ?>')">
                                    Editar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para editar meta tags -->
    <div class="modal" id="metaEditorModal">
        <div class="modal__overlay"></div>
        <div class="modal__content" style="max-width:600px">
            <div class="modal__header">
                <h3 id="metaEditorTitle">Editar Meta Tags</h3>
                <button type="button" data-modal-close class="modal__close">&times;</button>
            </div>
            <form method="POST" action="<?= url('/admin/seo/metatags') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="page" id="metaEditorPage">
                <div class="modal__body" style="padding:var(--spacing-6)">
                    <div class="form-group">
                        <label class="form-label">T&iacute;tulo SEO</label>
                        <input type="text" name="seo_title" id="metaEditorSeoTitle" class="form-control"
                               data-maxlength="60" placeholder="T&iacute;tulo para motores de b&uacute;squeda">
                        <small class="form-hint">Ideal: 30-60 caracteres.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Descripci&oacute;n SEO</label>
                        <textarea name="seo_description" id="metaEditorSeoDesc" class="form-control" rows="3"
                                  data-maxlength="160" placeholder="Descripci&oacute;n para resultados de b&uacute;squeda"></textarea>
                        <small class="form-hint">Ideal: 80-160 caracteres.</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keywords</label>
                        <input type="text" name="seo_keywords" id="metaEditorKeywords" class="form-control"
                               placeholder="palabra1, palabra2, palabra3">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Imagen OG</label>
                        <input type="text" name="seo_image" id="metaEditorImage" class="form-control"
                               placeholder="img/og/página.jpg">
                        <small class="form-hint">Ruta relativa a /assets/.</small>
                    </div>

                    <!-- Preview de Google -->
                    <div style="margin-top:var(--spacing-4);border:1px solid var(--color-border);border-radius:var(--radius-md);padding:var(--spacing-4)">
                        <small style="color:var(--color-gray);display:block;margin-bottom:4px">Preview en Google:</small>
                        <div style="font-size:1.1rem;color:#1a0dab" id="previewTitle">T&iacute;tulo de la p&aacute;gina</div>
                        <div style="font-size:0.8rem;color:#006621" id="previewUrl"><?= e(SITE_URL) ?>/</div>
                        <div style="font-size:0.8rem;color:#545454" id="previewDesc">Descripci&oacute;n de la p&aacute;gina...</div>
                    </div>
                </div>
                <div class="modal__footer">
                    <button type="button" data-modal-close class="btn btn--outline">Cancelar</button>
                    <button type="submit" class="btn btn--primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openMetaEditor(key, label, title, desc, keywords, image) {
        document.getElementById('metaEditorPage').value = key;
        document.getElementById('metaEditorTitle').textContent = 'Meta Tags: ' + label;
        document.getElementById('metaEditorSeoTitle').value = title;
        document.getElementById('metaEditorSeoDesc').value = desc;
        document.getElementById('metaEditorKeywords').value = keywords;
        document.getElementById('metaEditorImage').value = image;
        document.getElementById('previewTitle').textContent = title || label;
        document.getElementById('previewDesc').textContent = desc || 'Sin descripci\u00f3n configurada';
        document.getElementById('metaEditorModal').classList.add('modal--visible');

        var titleInput = document.getElementById('metaEditorSeoTitle');
        var descInput = document.getElementById('metaEditorSeoDesc');
        titleInput.addEventListener('input', function() {
            document.getElementById('previewTitle').textContent = this.value || label;
        });
        descInput.addEventListener('input', function() {
            document.getElementById('previewDesc').textContent = this.value || 'Sin descripci\u00f3n';
        });
    }
    </script>

<?php elseif ($currentTab === 'schema'): ?>
    <!-- ═══════ TAB 3: Schema.org ═══════ -->
    <form method="POST" action="<?= url('/admin/seo/schema') ?>">
        <?= csrf_field() ?>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-6)">
            <div>
                <div class="admin-card" style="margin-bottom:var(--spacing-6)">
                    <div style="padding:var(--spacing-6)">
                        <h3 style="margin:0 0 var(--spacing-4)">Datos del Negocio</h3>

                        <div class="form-group">
                            <label class="form-label">Tipo de organizaci&oacute;n</label>
                            <select name="schema_type" class="form-control">
                                <?php
                                $types = ['LocalBusiness', 'Organization', 'Store', 'Restaurant', 'ProfessionalService'];
                                foreach ($types as $t):
                                ?>
                                    <option value="<?= $t ?>" <?= ($schema['schema_type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="schema_name" class="form-control"
                                   value="<?= e($schema['schema_name'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Descripci&oacute;n</label>
                            <textarea name="schema_description" class="form-control" rows="3"><?= e($schema['schema_description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Direcci&oacute;n</label>
                            <input type="text" name="schema_address" class="form-control"
                                   value="<?= e($schema['schema_address'] ?? '') ?>" placeholder="Av. Principal 123">
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-3)">
                            <div class="form-group">
                                <label class="form-label">Localidad</label>
                                <input type="text" name="schema_locality" class="form-control"
                                       value="<?= e($schema['schema_locality'] ?? 'Purranque') ?>">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Regi&oacute;n</label>
                                <input type="text" name="schema_region" class="form-control"
                                       value="<?= e($schema['schema_region'] ?? 'Los Lagos') ?>">
                            </div>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-3)">
                            <div class="form-group">
                                <label class="form-label">Tel&eacute;fono</label>
                                <input type="tel" name="schema_phone" class="form-control"
                                       value="<?= e($schema['schema_phone'] ?? '') ?>" placeholder="+56 9 1234 5678">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" name="schema_email" class="form-control"
                                       value="<?= e($schema['schema_email'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">URL del logo</label>
                            <input type="text" name="schema_logo" class="form-control"
                                   value="<?= e($schema['schema_logo'] ?? '') ?>" placeholder="img/logo.png">
                            <small class="form-hint">Ruta relativa a /assets/.</small>
                        </div>
                    </div>
                </div>

                <div class="admin-card" style="margin-bottom:var(--spacing-6)">
                    <div style="padding:var(--spacing-6)">
                        <h3 style="margin:0 0 var(--spacing-4)">Redes Sociales</h3>

                        <div class="form-group">
                            <label class="form-label">Facebook URL</label>
                            <input type="url" name="schema_facebook" class="form-control"
                                   value="<?= e($schema['schema_facebook'] ?? '') ?>"
                                   placeholder="https://facebook.com/tu-página">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Instagram URL</label>
                            <input type="url" name="schema_instagram" class="form-control"
                                   value="<?= e($schema['schema_instagram'] ?? '') ?>"
                                   placeholder="https://instagram.com/tu-cuenta">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Twitter/X URL</label>
                            <input type="url" name="schema_twitter" class="form-control"
                                   value="<?= e($schema['schema_twitter'] ?? '') ?>"
                                   placeholder="https://x.com/tu-cuenta">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn--primary">Guardar Schema.org</button>
            </div>

            <!-- Preview JSON-LD -->
            <div>
                <div class="admin-card" style="position:sticky;top:var(--spacing-6)">
                    <div style="padding:var(--spacing-6)">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--spacing-4)">
                            <h3 style="margin:0">Preview JSON-LD</h3>
                            <button type="button" class="btn btn--outline btn--sm" onclick="copyJsonLd()">Copiar</button>
                        </div>
                        <pre id="jsonLdPreview" style="background:var(--color-dark);color:#a5f3fc;padding:var(--spacing-4);border-radius:var(--radius-md);font-size:0.75rem;overflow-x:auto;max-height:500px;line-height:1.5"><?php
                            $sameAs = array_filter([
                                $schema['schema_facebook'] ?? '',
                                $schema['schema_instagram'] ?? '',
                                $schema['schema_twitter'] ?? '',
                            ]);
                            $jsonLd = [
                                '@context' => 'https://schema.org',
                                '@type' => $schema['schema_type'] ?? 'LocalBusiness',
                                'name' => $schema['schema_name'] ?? SITE_NAME,
                                'description' => $schema['schema_description'] ?? '',
                                'url' => SITE_URL,
                                'address' => [
                                    '@type' => 'PostalAddress',
                                    'streetAddress' => $schema['schema_address'] ?? '',
                                    'addressLocality' => $schema['schema_locality'] ?? 'Purranque',
                                    'addressRegion' => $schema['schema_region'] ?? 'Los Lagos',
                                    'addressCountry' => 'CL',
                                ],
                            ];
                            if (!empty($schema['schema_phone'])) $jsonLd['telephone'] = $schema['schema_phone'];
                            if (!empty($schema['schema_email'])) $jsonLd['email'] = $schema['schema_email'];
                            if (!empty($schema['schema_logo']))  $jsonLd['logo'] = asset($schema['schema_logo']);
                            if (!empty($sameAs)) $jsonLd['sameAs'] = array_values($sameAs);

                            echo e(json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
                        ?></pre>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
    function copyJsonLd() {
        var text = document.getElementById('jsonLdPreview').textContent;
        var wrapper = '<script type="application/ld+json">\n' + text + '\n<\/script>';
        if (navigator.clipboard) {
            navigator.clipboard.writeText(wrapper).then(function() {
                alert('JSON-LD copiado al portapapeles');
            });
        }
    }
    </script>

<?php elseif ($currentTab === 'redirects'): ?>
    <!-- ═══════ TAB 4: Redirecciones ═══════ -->

    <!-- Formulario para crear redirect -->
    <div class="admin-card" style="margin-bottom:var(--spacing-6)">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Nueva redirecci&oacute;n</h3>
            <form method="POST" action="<?= url('/admin/seo/redirects') ?>" style="display:flex;gap:var(--spacing-3);align-items:flex-end;flex-wrap:wrap">
                <?= csrf_field() ?>

                <div class="form-group" style="flex:1;min-width:200px;margin:0">
                    <label class="form-label">URL origen</label>
                    <input type="text" name="url_origen" class="form-control"
                           placeholder="/ruta-antigua" required>
                </div>

                <div class="form-group" style="flex:1;min-width:200px;margin:0">
                    <label class="form-label">URL destino</label>
                    <input type="text" name="url_destino" class="form-control"
                           placeholder="/ruta-nueva" required>
                </div>

                <div class="form-group" style="margin:0">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-control" style="width:100px">
                        <option value="301">301</option>
                        <option value="302">302</option>
                    </select>
                </div>

                <button type="submit" class="btn btn--primary btn--sm">Crear</button>
            </form>
        </div>
    </div>

    <!-- Listado de redirects -->
    <?php if (!empty($redirects)): ?>
        <div class="admin-card">
            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>URL Origen</th>
                            <th>URL Destino</th>
                            <th>Tipo</th>
                            <th>Hits</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($redirects as $r): ?>
                            <tr>
                                <td><code style="font-size:0.8rem"><?= e($r['url_origen']) ?></code></td>
                                <td><code style="font-size:0.8rem"><?= e($r['url_destino']) ?></code></td>
                                <td><span class="badge <?= $r['tipo'] == 301 ? 'badge--info' : 'badge--warning' ?>"><?= $r['tipo'] ?></span></td>
                                <td><?= number_format($r['hits']) ?></td>
                                <td>
                                    <label class="toggle-switch">
                                        <input type="checkbox"
                                               <?= $r['activo'] ? 'checked' : '' ?>
                                               data-toggle-url="<?= url('/admin/seo/redirects/toggle/' . $r['id']) ?>">
                                        <span class="toggle-switch__slider"></span>
                                    </label>
                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn--danger btn--sm"
                                            data-delete-url="<?= url('/admin/seo/redirects/eliminar/' . $r['id']) ?>"
                                            data-delete-name="redirecci&oacute;n <?= e($r['url_origen']) ?>">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php else: ?>
        <div class="admin-card">
            <div style="text-align:center;padding:3rem;color:var(--color-gray)">
                <p><strong>Sin redirecciones</strong></p>
                <p style="font-size:0.875rem">Crea redirecciones 301/302 para gestionar URLs antiguas.</p>
            </div>
        </div>
    <?php endif; ?>

<?php elseif ($currentTab === 'sitemap'): ?>
    <!-- ═══════ TAB 5: Sitemap ═══════ -->
    <div class="admin-card">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Sitemap XML</h3>

            <?php if ($sitemapExists ?? false): ?>
                <div style="background:var(--color-light);padding:var(--spacing-4);border-radius:var(--radius-md);margin-bottom:var(--spacing-4)">
                    <p style="margin:0 0 4px">
                        <strong>Estado:</strong>
                        <span class="badge badge--success">Generado</span>
                    </p>
                    <p style="margin:0 0 4px;font-size:0.875rem;color:var(--color-gray)">
                        &Uacute;ltima generaci&oacute;n: <?= e($sitemapDate ?? 'Desconocida') ?>
                    </p>
                    <p style="margin:0;font-size:0.875rem">
                        <a href="<?= url('/sitemap.xml') ?>" target="_blank">Ver sitemap.xml &rarr;</a>
                    </p>
                </div>
            <?php else: ?>
                <div style="background:#fef3c7;padding:var(--spacing-4);border-radius:var(--radius-md);margin-bottom:var(--spacing-4)">
                    <p style="margin:0;color:#92400e">
                        <strong>El sitemap a&uacute;n no ha sido generado.</strong> Haz clic en el bot&oacute;n para crearlo.
                    </p>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?= url('/admin/seo/sitemap') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn--primary">
                    <?= ($sitemapExists ?? false) ? 'Regenerar sitemap' : 'Generar sitemap' ?>
                </button>
            </form>

            <div style="margin-top:var(--spacing-6);border-top:1px solid var(--color-border);padding-top:var(--spacing-4)">
                <h4 style="margin:0 0 var(--spacing-3)">El sitemap incluir&aacute;:</h4>
                <ul style="font-size:0.875rem;color:var(--color-gray);margin:0;padding-left:var(--spacing-5)">
                    <li>P&aacute;gina principal</li>
                    <li>Todos los comercios activos</li>
                    <li>Todas las categor&iacute;as activas</li>
                    <li>Todas las fechas especiales activas</li>
                    <li>Todas las noticias publicadas</li>
                    <li>Mapa, buscador y listado de noticias</li>
                </ul>
            </div>
        </div>
    </div>

<?php elseif ($currentTab === 'tools'): ?>
    <!-- ═══════ TAB 6: Herramientas SEO ═══════ -->

    <!-- Test de Meta Tags -->
    <div class="admin-card" style="margin-bottom:var(--spacing-6)">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Test de Meta Tags</h3>
            <p style="font-size:0.875rem;color:var(--color-gray);margin:0 0 var(--spacing-4)">
                Ingresa una URL interna para ver c&oacute;mo se ve en Google y redes sociales.
            </p>

            <div style="display:flex;gap:var(--spacing-3);margin-bottom:var(--spacing-4)">
                <input type="text" id="testUrl" class="form-control" placeholder="/comercio/mi-comercio" style="flex:1">
                <button type="button" class="btn btn--primary btn--sm" onclick="testMetaTags()">Analizar</button>
            </div>

            <div id="testResult" style="display:none">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--spacing-4)">
                    <!-- Preview Google -->
                    <div style="border:1px solid var(--color-border);border-radius:var(--radius-md);padding:var(--spacing-4)">
                        <small style="color:var(--color-gray);display:block;margin-bottom:var(--spacing-3);font-weight:600">Preview en Google</small>
                        <div id="testGoogleTitle" style="font-size:1.1rem;color:#1a0dab;margin-bottom:2px"></div>
                        <div id="testGoogleUrl" style="font-size:0.8rem;color:#006621;margin-bottom:4px"></div>
                        <div id="testGoogleDesc" style="font-size:0.8rem;color:#545454"></div>
                    </div>

                    <!-- Preview Facebook -->
                    <div style="border:1px solid var(--color-border);border-radius:var(--radius-md);padding:var(--spacing-4)">
                        <small style="color:var(--color-gray);display:block;margin-bottom:var(--spacing-3);font-weight:600">Preview en Facebook</small>
                        <div id="testFbImage" style="background:#f3f4f6;height:120px;border-radius:4px;margin-bottom:var(--spacing-3);display:flex;align-items:center;justify-content:center;color:var(--color-gray);font-size:0.8rem">Sin imagen OG</div>
                        <div id="testFbTitle" style="font-weight:600;font-size:0.9rem;margin-bottom:2px"></div>
                        <div id="testFbDesc" style="font-size:0.8rem;color:var(--color-gray)"></div>
                    </div>
                </div>

                <!-- Checklist SEO -->
                <div style="margin-top:var(--spacing-4);border:1px solid var(--color-border);border-radius:var(--radius-md);padding:var(--spacing-4)">
                    <strong style="display:block;margin-bottom:var(--spacing-3)">Checklist SEO</strong>
                    <ul id="testChecklist" style="list-style:none;padding:0;margin:0;font-size:0.875rem">
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Sugerencias SEO -->
    <div class="admin-card">
        <div style="padding:var(--spacing-6)">
            <h3 style="margin:0 0 var(--spacing-4)">Sugerencias SEO</h3>
            <ul style="font-size:0.875rem;margin:0;padding-left:var(--spacing-5);line-height:2">
                <li>Aseg&uacute;rate de que todas las p&aacute;ginas tengan t&iacute;tulo y descripci&oacute;n &uacute;nicos (<a href="<?= url('/admin/seo?tab=metatags') ?>">Meta Tags</a>).</li>
                <li>Configura los datos de Schema.org para mejorar los Rich Snippets (<a href="<?= url('/admin/seo?tab=schema') ?>">Schema.org</a>).</li>
                <li>Genera el sitemap.xml y env&iacute;alo a Google Search Console (<a href="<?= url('/admin/seo?tab=sitemap') ?>">Sitemap</a>).</li>
                <li>Configura las redirecciones 301 para URLs antiguas de la versi&oacute;n anterior (<a href="<?= url('/admin/seo?tab=redirects') ?>">Redirecciones</a>).</li>
                <li>Usa im&aacute;genes OG de 1200x630px para mejor visualizaci&oacute;n en redes sociales.</li>
                <li>Mantener los t&iacute;tulos entre 30-60 caracteres y descripciones entre 80-160 caracteres.</li>
                <li>A&ntilde;ade el c&oacute;digo de Google Analytics y verifica Search Console (<a href="<?= url('/admin/seo?tab=config') ?>">Config Global</a>).</li>
            </ul>
        </div>
    </div>

    <script>
    function testMetaTags() {
        var url = document.getElementById('testUrl').value.trim();
        if (!url) { alert('Ingresa una URL'); return; }
        if (!url.startsWith('/')) url = '/' + url;

        var resultDiv = document.getElementById('testResult');
        var fullUrl = '<?= e(SITE_URL) ?>' + url;

        // Fetch the page and extract meta tags
        fetch(url, {headers: {'Accept': 'text/html'}})
        .then(function(r) { return r.text(); })
        .then(function(html) {
            var parser = new DOMParser();
            var doc = parser.parseFromString(html, 'text/html');

            var title = doc.querySelector('title') ? doc.querySelector('title').textContent : '';
            var desc = '';
            var ogTitle = '';
            var ogDesc = '';
            var ogImage = '';

            var metaDesc = doc.querySelector('meta[name="description"]');
            if (metaDesc) desc = metaDesc.getAttribute('content') || '';

            var metaOgTitle = doc.querySelector('meta[property="og:title"]');
            if (metaOgTitle) ogTitle = metaOgTitle.getAttribute('content') || '';

            var metaOgDesc = doc.querySelector('meta[property="og:description"]');
            if (metaOgDesc) ogDesc = metaOgDesc.getAttribute('content') || '';

            var metaOgImage = doc.querySelector('meta[property="og:image"]');
            if (metaOgImage) ogImage = metaOgImage.getAttribute('content') || '';

            // Google preview
            document.getElementById('testGoogleTitle').textContent = title || 'Sin t\u00edtulo';
            document.getElementById('testGoogleUrl').textContent = fullUrl;
            document.getElementById('testGoogleDesc').textContent = desc || 'Sin descripci\u00f3n meta';

            // Facebook preview
            document.getElementById('testFbTitle').textContent = ogTitle || title || 'Sin t\u00edtulo OG';
            document.getElementById('testFbDesc').textContent = ogDesc || desc || '';
            var fbImg = document.getElementById('testFbImage');
            if (ogImage) {
                fbImg.innerHTML = '<img src="' + ogImage + '" style="max-height:120px;max-width:100%;border-radius:4px" onerror="this.parentNode.textContent=\'Imagen no encontrada\'">';
            } else {
                fbImg.textContent = 'Sin imagen OG';
            }

            // Checklist
            var checks = [];
            checks.push({ok: title.length > 0, text: 'T\u00edtulo presente (' + title.length + ' chars)'});
            checks.push({ok: title.length >= 30 && title.length <= 60, text: 'T\u00edtulo entre 30-60 chars'});
            checks.push({ok: desc.length > 0, text: 'Descripci\u00f3n presente (' + desc.length + ' chars)'});
            checks.push({ok: desc.length >= 80 && desc.length <= 160, text: 'Descripci\u00f3n entre 80-160 chars'});
            checks.push({ok: ogTitle.length > 0, text: 'OG Title presente'});
            checks.push({ok: ogDesc.length > 0, text: 'OG Description presente'});
            checks.push({ok: ogImage.length > 0, text: 'OG Image presente'});

            var canonical = doc.querySelector('link[rel="canonical"]');
            checks.push({ok: !!canonical, text: 'URL can\u00f3nica presente'});

            var h1 = doc.querySelector('h1');
            checks.push({ok: !!h1, text: 'H1 presente'});

            var checklistEl = document.getElementById('testChecklist');
            checklistEl.innerHTML = '';
            checks.forEach(function(c) {
                var li = document.createElement('li');
                li.style.marginBottom = '4px';
                li.innerHTML = '<span style="color:' + (c.ok ? '#059669' : '#dc2626') + ';margin-right:6px">' + (c.ok ? '\u2714' : '\u2718') + '</span>' + c.text;
                checklistEl.appendChild(li);
            });

            resultDiv.style.display = 'block';
        })
        .catch(function(err) {
            alert('Error al cargar la p\u00e1gina: ' + err.message);
        });
    }
    </script>

<?php endif; ?>
