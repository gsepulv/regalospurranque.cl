/**
 * Regalos Purranque v2 - JavaScript del panel de administracion
 */
(function() {
    'use strict';

    // ── 1. Sidebar toggle (movil) ──────────────────────────────
    var sidebar = document.getElementById('adminSidebar');
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebarOverlay = document.getElementById('sidebarOverlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar--open');
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('sidebar-overlay--visible');
            }
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('sidebar--open');
            sidebarOverlay.classList.remove('sidebar-overlay--visible');
        });
    }

    // ── 2. Toasts auto-hide ────────────────────────────────────
    document.querySelectorAll('.toast').forEach(function(toast) {
        // Boton cerrar
        var closeBtn = toast.querySelector('[data-toast-close]');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                toast.style.display = 'none';
            });
        }
        // Auto-ocultar en 5 segundos (solo los que no son inline)
        if (!toast.classList.contains('toast--inline')) {
            setTimeout(function() {
                toast.style.transition = 'opacity 0.3s';
                toast.style.opacity = '0';
                setTimeout(function() { toast.style.display = 'none'; }, 300);
            }, 5000);
        }
    });

    // ── 3. Toggle activo/inactivo via AJAX ─────────────────────
    document.querySelectorAll('[data-toggle-url]').forEach(function(toggle) {
        toggle.addEventListener('change', function() {
            var url = this.getAttribute('data-toggle-url');
            var checkbox = this;
            var csrfToken = document.querySelector('input[name="_csrf"]');
            if (!csrfToken) csrfToken = document.querySelector('meta[name="csrf-token"]');
            var token = csrfToken ? (csrfToken.value || csrfToken.content || csrfToken.getAttribute('content')) : '';

            var body = new FormData();
            body.append('_csrf', token);

            fetch(url, {
                method: 'POST',
                body: body
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (!data.ok) {
                    checkbox.checked = !checkbox.checked;
                    alert(data.error || 'Error al actualizar');
                }
                // Actualizar token CSRF si se regenero
                if (data.csrf) {
                    document.querySelectorAll('input[name="_csrf"]').forEach(function(inp) {
                        inp.value = data.csrf;
                    });
                }
            })
            .catch(function() {
                checkbox.checked = !checkbox.checked;
                alert('Error de conexion');
            });
        });
    });

    // ── 4. Modal de confirmacion de eliminacion ────────────────
    var deleteModal = document.getElementById('deleteModal');
    var deleteForm = document.getElementById('deleteForm');
    var deleteModalText = document.getElementById('deleteModalText');

    document.querySelectorAll('[data-delete-url]').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            var url = this.getAttribute('data-delete-url');
            var name = this.getAttribute('data-delete-name') || 'este elemento';

            if (deleteForm) deleteForm.action = url;
            if (deleteModalText) {
                deleteModalText.textContent = '¿Estas seguro de que deseas eliminar "' + name + '"? Esta accion no se puede deshacer.';
            }
            if (deleteModal) deleteModal.classList.add('modal--visible');
        });
    });

    // Cerrar modal
    document.querySelectorAll('[data-modal-close]').forEach(function(el) {
        el.addEventListener('click', function() {
            var modal = this.closest('.modal');
            if (modal) {
                modal.classList.remove('modal--visible');
                modal.classList.remove('modal--open');
            }
        });
    });

    // ── 5. Auto-generar slug desde nombre ──────────────────────
    document.querySelectorAll('[data-slug-source]').forEach(function(input) {
        var form = input.closest('form');
        if (!form) return;
        var slugInput = form.querySelector('[data-slug-target]');
        if (!slugInput) return;

        var manuallyEdited = false;
        slugInput.addEventListener('input', function() {
            manuallyEdited = true;
        });

        input.addEventListener('input', function() {
            if (manuallyEdited && slugInput.value !== '') return;
            slugInput.value = slugify(this.value);
            manuallyEdited = false;
        });
    });

    function slugify(text) {
        var map = {'a':'a','e':'e','i':'i','o':'o','u':'u','n':'n','u':'u',
                   '\u00e1':'a','\u00e9':'e','\u00ed':'i','\u00f3':'o','\u00fa':'u',
                   '\u00f1':'n','\u00fc':'u'};
        text = text.toLowerCase();
        text = text.replace(/[áéíóúñü]/g, function(c) { return map[c] || c; });
        text = text.replace(/[^a-z0-9\-]/g, '-');
        text = text.replace(/-+/g, '-');
        text = text.replace(/^-|-$/g, '');
        return text;
    }

    // ── 6. Preview de imagen al seleccionar archivo ────────────
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function(input) {
        input.addEventListener('change', function() {
            var previewId = this.getAttribute('data-preview');
            var preview = document.getElementById(previewId);
            if (!preview) return;

            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    });

    // ── 7. Contador de caracteres en textareas ─────────────────
    document.querySelectorAll('textarea[data-maxlength]').forEach(function(textarea) {
        var max = parseInt(textarea.getAttribute('data-maxlength'), 10);
        var counter = document.createElement('div');
        counter.className = 'text-muted';
        counter.style.fontSize = '0.75rem';
        counter.style.textAlign = 'right';
        counter.style.marginTop = '4px';
        textarea.parentNode.appendChild(counter);

        function updateCounter() {
            var len = textarea.value.length;
            counter.textContent = len + ' / ' + max;
            counter.style.color = len > max ? '#dc2626' : '';
        }

        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });

    // ── 8. Filtros auto-submit ─────────────────────────────────
    document.querySelectorAll('.filter-form select').forEach(function(select) {
        select.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });

    // ── 9. Collapsible sections ────────────────────────────────
    document.querySelectorAll('.collapsible__header').forEach(function(header) {
        header.addEventListener('click', function() {
            this.classList.toggle('collapsed');
            var body = this.nextElementSibling;
            if (body) {
                body.classList.toggle('collapsed');
                if (!body.classList.contains('collapsed')) {
                    body.style.maxHeight = body.scrollHeight + 'px';
                }
            }
        });
    });

    // ── 10. Checkboxes de fechas: mostrar campos extra ─────────
    document.querySelectorAll('.fecha-checkbox').forEach(function(cb) {
        cb.addEventListener('change', function() {
            var fields = document.getElementById('fecha-fields-' + this.value);
            if (fields) {
                if (this.checked) {
                    fields.classList.add('visible');
                } else {
                    fields.classList.remove('visible');
                }
            }
        });
    });

    // ── 11. Select all / deselect all checkboxes ───────────────
    document.querySelectorAll('[data-check-all]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var group = this.getAttribute('data-check-all');
            var container = document.getElementById(group);
            if (!container) return;
            var checkboxes = container.querySelectorAll('input[type="checkbox"]');
            var allChecked = Array.from(checkboxes).every(function(cb) { return cb.checked; });
            checkboxes.forEach(function(cb) {
                cb.checked = !allChecked;
                cb.dispatchEvent(new Event('change'));
            });
        });
    });

    // ── 12. Plan update AJAX ───────────────────────────────────
    document.querySelectorAll('.plan-select[data-plan-url]').forEach(function(select) {
        select.addEventListener('change', function() {
            var url = this.getAttribute('data-plan-url');
            var comercioId = this.getAttribute('data-comercio-id');
            var plan = this.value;
            var selectEl = this;

            var csrfInput = document.querySelector('#deleteForm input[name="_csrf"]');
            var token = csrfInput ? csrfInput.value : '';

            var body = new FormData();
            body.append('_csrf', token);
            body.append('comercio_id', comercioId);
            body.append('plan', plan);

            fetch(url, { method: 'POST', body: body })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.ok) {
                    selectEl.style.borderColor = '#059669';
                    setTimeout(function() { selectEl.style.borderColor = ''; }, 1500);
                } else {
                    alert(data.error || 'Error');
                }
            })
            .catch(function() { alert('Error de conexion'); });
        });
    });

    // ── 13. Confirmacion data-confirm ──────────────────────
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('[data-confirm]');
        if (btn) {
            var message = btn.getAttribute('data-confirm');
            if (!confirm(message)) {
                e.preventDefault();
            }
        }
    });

    // ── 14. Validacion basica client-side ──────────────────────
    document.querySelectorAll('form[data-validate]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var valid = true;
            this.querySelectorAll('[required]').forEach(function(input) {
                if (!input.value.trim()) {
                    input.style.borderColor = '#dc2626';
                    valid = false;
                } else {
                    input.style.borderColor = '';
                }
            });
            if (!valid) {
                e.preventDefault();
                alert('Por favor completa todos los campos obligatorios');
            }
        });
    });

})();
