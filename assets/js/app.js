/**
 * ==========================================================================
 * Regalos Purranque v2 - JavaScript principal del sitio publico
 * Directorio comercial de Purranque, Chile
 * ==========================================================================
 *
 * Funcionalidades:
 * - Toggle del menu de navegacion movil
 * - Notificaciones toast con auto-cierre
 * - Apertura y cierre de modales
 * - Desplazamiento suave para enlaces ancla
 * - Carga diferida de imagenes (lazy loading)
 * - Validacion basica del formulario de busqueda
 * - Boton "Volver arriba"
 *
 * Sin dependencias externas. JavaScript puro (vanilla).
 */

document.addEventListener('DOMContentLoaded', function () {

    /* ======================================================================
       1. Toggle del menu de navegacion movil
       ====================================================================== */
    (function initNavToggle() {
        var navToggle = document.querySelector('.nav__toggle');
        var nav = document.querySelector('.nav');

        if (!navToggle || !nav) return;

        navToggle.addEventListener('click', function () {
            nav.classList.toggle('nav--open');

            // Actualizar atributo aria para accesibilidad
            var expanded = nav.classList.contains('nav--open');
            navToggle.setAttribute('aria-expanded', String(expanded));
        });

        // Cerrar menu al hacer clic en un enlace (movil)
        var navLinks = nav.querySelectorAll('.nav__links a');
        navLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                nav.classList.remove('nav--open');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });

        // Cerrar menu al hacer clic fuera de el
        document.addEventListener('click', function (e) {
            if (!nav.contains(e.target) && nav.classList.contains('nav--open')) {
                nav.classList.remove('nav--open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });
    })();

    /* ======================================================================
       2. Notificaciones toast - auto-cierre y cierre manual
       ====================================================================== */
    (function initToasts() {
        /** Tiempo en milisegundos antes de ocultar automaticamente */
        var AUTO_DISMISS_MS = 5000;

        /**
         * Oculta un toast con animacion de desvanecimiento.
         * @param {HTMLElement} toast - Elemento del toast
         */
        function dismissToast(toast) {
            if (!toast || toast.classList.contains('toast--hiding')) return;

            toast.classList.add('toast--hiding');

            toast.addEventListener('animationend', function () {
                toast.remove();
            }, { once: true });

            // Respaldo por si animationend no se dispara
            setTimeout(function () {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 400);
        }

        /**
         * Inicializa un toast individual con temporizador y boton de cierre.
         * @param {HTMLElement} toast - Elemento del toast
         */
        function setupToast(toast) {
            // Auto-cierre despues de 5 segundos
            var timer = setTimeout(function () {
                dismissToast(toast);
            }, AUTO_DISMISS_MS);

            // Cierre manual con boton
            var closeBtn = toast.querySelector('.toast__close');
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    clearTimeout(timer);
                    dismissToast(toast);
                });
            }

            // Tambien cerrar al hacer clic en el toast completo
            toast.addEventListener('click', function () {
                clearTimeout(timer);
                dismissToast(toast);
            });
        }

        // Inicializar toasts existentes en el DOM
        var toasts = document.querySelectorAll('.toast');
        toasts.forEach(setupToast);

        // Observar si se agregan nuevos toasts dinamicamente
        var toastContainer = document.querySelector('.toast-container');
        if (toastContainer) {
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    mutation.addedNodes.forEach(function (node) {
                        if (node.nodeType === 1 && node.classList.contains('toast')) {
                            setupToast(node);
                        }
                    });
                });
            });
            observer.observe(toastContainer, { childList: true });
        }
    })();

    /* ======================================================================
       3. Modales - abrir, cerrar, tecla Escape
       ====================================================================== */
    (function initModals() {
        /**
         * Abre un modal por su ID.
         * @param {string} modalId - ID del elemento modal
         */
        function openModal(modalId) {
            var modal = document.getElementById(modalId);
            if (!modal) return;

            modal.classList.add('modal--active');
            document.body.style.overflow = 'hidden';

            // Enfocar primer elemento interactivo dentro del modal
            var firstFocusable = modal.querySelector(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );
            if (firstFocusable) {
                setTimeout(function () {
                    firstFocusable.focus();
                }, 100);
            }
        }

        /**
         * Cierra un modal.
         * @param {HTMLElement} modal - Elemento modal a cerrar
         */
        function closeModal(modal) {
            if (!modal) return;

            modal.classList.remove('modal--active');
            document.body.style.overflow = '';
        }

        /**
         * Cierra todos los modales abiertos.
         */
        function closeAllModals() {
            var openModals = document.querySelectorAll('.modal--active');
            openModals.forEach(closeModal);
        }

        // Botones que abren modales (data-modal-open="idDelModal")
        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('[data-modal-open]');
            if (trigger) {
                e.preventDefault();
                var modalId = trigger.getAttribute('data-modal-open');
                openModal(modalId);
            }
        });

        // Botones que cierran modales (data-modal-close)
        document.addEventListener('click', function (e) {
            var closer = e.target.closest('[data-modal-close]');
            if (closer) {
                e.preventDefault();
                var modal = closer.closest('.modal');
                closeModal(modal);
            }
        });

        // Cerrar al hacer clic en el overlay
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('modal__overlay')) {
                var modal = e.target.closest('.modal');
                closeModal(modal);
            }
        });

        // Cerrar con la tecla Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeAllModals();
            }
        });
    })();

    /* ======================================================================
       4. Desplazamiento suave para enlaces ancla
       ====================================================================== */
    (function initSmoothScroll() {
        document.addEventListener('click', function (e) {
            var link = e.target.closest('a[href^="#"]');
            if (!link) return;

            var targetId = link.getAttribute('href');

            // Ignorar enlaces vacios o solo "#"
            if (!targetId || targetId === '#') return;

            var targetElement = document.querySelector(targetId);
            if (!targetElement) return;

            e.preventDefault();

            // Calcular posicion considerando la altura de la navegacion fija
            var nav = document.querySelector('.nav');
            var navHeight = nav ? nav.offsetHeight : 0;
            var targetPosition = targetElement.getBoundingClientRect().top + window.pageYOffset - navHeight - 16;

            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });

            // Actualizar la URL sin recargar la pagina
            if (history.pushState) {
                history.pushState(null, null, targetId);
            }
        });
    })();

    /* ======================================================================
       5. Carga diferida de imagenes (lazy loading con IntersectionObserver)
       ====================================================================== */
    (function initLazyLoading() {
        var lazyImages = document.querySelectorAll('img[data-src]');

        if (lazyImages.length === 0) return;

        // Verificar soporte de IntersectionObserver
        if ('IntersectionObserver' in window) {
            var imageObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        var img = entry.target;
                        var src = img.getAttribute('data-src');

                        if (src) {
                            img.src = src;
                            img.removeAttribute('data-src');

                            // Agregar clase cuando la imagen termine de cargar
                            img.addEventListener('load', function () {
                                img.classList.add('loaded');
                            }, { once: true });

                            // Manejar error de carga
                            img.addEventListener('error', function () {
                                img.classList.add('error');
                            }, { once: true });
                        }

                        observer.unobserve(img);
                    }
                });
            }, {
                // Empezar a cargar un poco antes de que la imagen sea visible
                rootMargin: '100px 0px',
                threshold: 0.01
            });

            lazyImages.forEach(function (img) {
                imageObserver.observe(img);
            });
        } else {
            // Respaldo para navegadores sin soporte: cargar todas las imagenes
            lazyImages.forEach(function (img) {
                var src = img.getAttribute('data-src');
                if (src) {
                    img.src = src;
                    img.removeAttribute('data-src');
                }
            });
        }
    })();

    /* ======================================================================
       6. Formulario de busqueda - prevenir envio vacio
       ====================================================================== */
    (function initSearchForm() {
        var searchForms = document.querySelectorAll('.search-form');

        searchForms.forEach(function (form) {
            form.addEventListener('submit', function (e) {
                var input = form.querySelector('.search-input');
                if (!input) return;

                var query = input.value.trim();

                if (query.length === 0) {
                    e.preventDefault();

                    // Agregar efecto visual de error
                    input.classList.add('form-control--error');
                    input.focus();

                    // Remover el efecto despues de un momento
                    setTimeout(function () {
                        input.classList.remove('form-control--error');
                    }, 2000);

                    return false;
                }
            });
        });
    })();

    /* ======================================================================
       7. Boton "Volver arriba"
       ====================================================================== */
    (function initBackToTop() {
        var backToTopBtn = document.querySelector('.back-to-top');

        if (!backToTopBtn) return;

        /** Umbral de scroll en pixeles para mostrar el boton */
        var SCROLL_THRESHOLD = 300;

        // Controlar la visibilidad del boton segun la posicion de scroll
        function toggleBackToTop() {
            if (window.pageYOffset > SCROLL_THRESHOLD) {
                backToTopBtn.classList.add('back-to-top--visible');
            } else {
                backToTopBtn.classList.remove('back-to-top--visible');
            }
        }

        // Optimizar el evento scroll con requestAnimationFrame
        var ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(function () {
                    toggleBackToTop();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        // Desplazar al inicio al hacer clic
        backToTopBtn.addEventListener('click', function () {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Verificar estado inicial
        toggleBackToTop();
    })();

    /* ======================================================================
       8a. Header scroll effect (add shadow on scroll)
       ====================================================================== */
    (function initHeaderScroll() {
        var nav = document.querySelector('.nav');
        if (!nav) return;

        var SCROLL_THRESHOLD = 50;
        var scrolled = false;

        function updateNav() {
            var shouldScroll = window.pageYOffset > SCROLL_THRESHOLD;
            if (shouldScroll !== scrolled) {
                scrolled = shouldScroll;
                if (scrolled) {
                    nav.classList.add('nav--scrolled');
                } else {
                    nav.classList.remove('nav--scrolled');
                }
            }
        }

        var ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(function () {
                    updateNav();
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });

        updateNav();
    })();

    /* ======================================================================
       8b. Fade-in animations on scroll (IntersectionObserver)
       ====================================================================== */
    (function initFadeInAnimations() {
        var animElements = document.querySelectorAll('.fade-in-up');
        if (!animElements.length || !('IntersectionObserver' in window)) return;

        // Respect prefers-reduced-motion
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            animElements.forEach(function (el) {
                el.style.opacity = '1';
                el.style.transform = 'none';
            });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up--visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        animElements.forEach(function (el) {
            observer.observe(el);
        });
    })();

    /* ======================================================================
       8c. Toggle sidebar admin (panel de administracion)
       ====================================================================== */
    (function initAdminSidebar() {
        var sidebarToggle = document.getElementById('sidebarToggle');
        var sidebar = document.getElementById('adminSidebar');

        if (!sidebarToggle || !sidebar) return;

        // Crear overlay para cerrar sidebar en móvil
        var overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);

        function toggleSidebar() {
            sidebar.classList.toggle('sidebar--open');
            overlay.classList.toggle('sidebar-overlay--visible');
        }

        function closeSidebar() {
            sidebar.classList.remove('sidebar--open');
            overlay.classList.remove('sidebar-overlay--visible');
        }

        sidebarToggle.addEventListener('click', toggleSidebar);
        overlay.addEventListener('click', closeSidebar);

        // Cerrar con Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && sidebar.classList.contains('sidebar--open')) {
                closeSidebar();
            }
        });
    })();

    /* ======================================================================
       9. Confirmación de acciones destructivas
       ====================================================================== */
    (function initConfirmActions() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-confirm]');
            if (!btn) return;

            var message = btn.getAttribute('data-confirm') || '¿Estás seguro?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    })();

    /* ======================================================================
       10. Tracking de impresiones de banners (IntersectionObserver)
       ====================================================================== */
    (function initBannerTracking() {
        var banners = document.querySelectorAll('[data-banner-id]');
        if (!banners.length || !('IntersectionObserver' in window)) return;

        var trackedBanners = {};

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var bannerId = entry.target.getAttribute('data-banner-id');
                    if (bannerId && !trackedBanners[bannerId]) {
                        trackedBanners[bannerId] = true;

                        fetch('/api/banner-track', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({
                                banner_id: parseInt(bannerId, 10),
                                tipo: 'impression'
                            })
                        });
                    }
                }
            });
        }, { threshold: 0.5 });

        banners.forEach(function (banner) {
            observer.observe(banner);
        });
    })();

    /* ======================================================================
       11. Tracking de visitas de pagina
       ====================================================================== */
    (function initPageTracking() {
        var path = window.location.pathname;
        var tipo = 'pagina';

        // Determinar tipo segun la ruta
        if (path === '/') tipo = 'home';
        else if (path.startsWith('/comercio/')) tipo = 'comercio';
        else if (path.startsWith('/categoria/')) tipo = 'categoria';
        else if (path.startsWith('/fecha/')) tipo = 'fecha';
        else if (path.startsWith('/noticia/')) tipo = 'noticia';
        else if (path === '/noticias') tipo = 'noticias';
        else if (path === '/buscar') tipo = 'buscar';
        else if (path === '/mapa') tipo = 'mapa';

        fetch('/api/track', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pagina: path, tipo: tipo })
        }).catch(function () { /* silenciar errores de tracking */ });
    })();

    /* ======================================================================
       12. Tracking de clics en WhatsApp
       ====================================================================== */
    (function initWhatsAppTracking() {
        document.addEventListener('click', function (e) {
            var link = e.target.closest('a[href*="wa.me"], a[href*="whatsapp.com"]');
            if (!link) return;

            var comercioId = link.getAttribute('data-comercio-id');
            if (comercioId) {
                fetch('/api/track', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        comercio_id: parseInt(comercioId, 10),
                        tipo: 'whatsapp'
                    })
                }).catch(function () {});
            }
        });
    })();

    /* ======================================================================
       13. Botones de compartir - Web Share API y tracking
       ====================================================================== */
    (function initShareButtons() {
        // Tracking de clics en share
        document.addEventListener('click', function (e) {
            var shareBtn = e.target.closest('[data-share]');
            if (!shareBtn) return;

            var red = shareBtn.getAttribute('data-share');
            var slug = shareBtn.getAttribute('data-share-slug') || '';
            var tipo = shareBtn.getAttribute('data-share-type') || '';

            fetch('/api/share-track', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ red: red, slug: slug, tipo: tipo })
            }).catch(function () {});
        });

        // Copiar enlace al portapapeles
        document.addEventListener('click', function (e) {
            var copyBtn = e.target.closest('[data-copy-url]');
            if (!copyBtn) return;

            e.preventDefault();
            var url = copyBtn.getAttribute('data-copy-url');
            var originalText = copyBtn.textContent;

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function () {
                    copyBtn.classList.add('copied');
                    copyBtn.innerHTML = copyBtn.querySelector('svg') ?
                        copyBtn.querySelector('svg').outerHTML + ' Copiado!' :
                        'Copiado!';
                    setTimeout(function () {
                        copyBtn.classList.remove('copied');
                        copyBtn.innerHTML = copyBtn.querySelector('svg') ?
                            copyBtn.querySelector('svg').outerHTML + ' Copiar enlace' :
                            'Copiar enlace';
                    }, 2000);
                });
            } else {
                // Fallback para navegadores sin clipboard API
                var textarea = document.createElement('textarea');
                textarea.value = url;
                textarea.style.position = 'fixed';
                textarea.style.left = '-9999px';
                document.body.appendChild(textarea);
                textarea.select();
                try {
                    document.execCommand('copy');
                    copyBtn.classList.add('copied');
                    var svgEl = copyBtn.querySelector('svg');
                    var svgHTML = svgEl ? svgEl.outerHTML + ' ' : '';
                    copyBtn.innerHTML = svgHTML + 'Copiado!';
                    setTimeout(function () {
                        copyBtn.classList.remove('copied');
                        copyBtn.innerHTML = svgHTML + 'Copiar enlace';
                    }, 2000);
                } catch (err) { /* silenciar */ }
                document.body.removeChild(textarea);
            }
        });

        // Web Share API nativo (boton especial si existe)
        var nativeShareBtn = document.querySelector('[data-native-share]');
        if (nativeShareBtn && navigator.share) {
            nativeShareBtn.style.display = '';
            nativeShareBtn.addEventListener('click', function (e) {
                e.preventDefault();
                navigator.share({
                    title: nativeShareBtn.getAttribute('data-share-title') || document.title,
                    url: nativeShareBtn.getAttribute('data-share-url') || window.location.href
                }).catch(function () {});
            });
        }
    })();

    /* ======================================================================
       14. Lightbox para galeria de fotos
       ====================================================================== */
    (function initLightbox() {
        var lightbox = document.getElementById('lightbox');
        if (!lightbox) return;

        var imgEl = lightbox.querySelector('.lightbox__img');
        var counterEl = lightbox.querySelector('.lightbox__counter');
        var prevBtn = lightbox.querySelector('.lightbox__prev');
        var nextBtn = lightbox.querySelector('.lightbox__next');
        var closeBtn = lightbox.querySelector('.lightbox__close');

        var images = [];
        var currentIndex = 0;

        // Recoger todas las imagenes del gallery-grid
        var galleryImages = document.querySelectorAll('.gallery-img[data-lightbox]');
        galleryImages.forEach(function (img, idx) {
            images.push({
                src: img.getAttribute('data-lightbox') || img.src,
                alt: img.alt || ''
            });

            img.addEventListener('click', function () {
                currentIndex = idx;
                openLightbox();
            });
        });

        if (images.length === 0) return;

        function openLightbox() {
            showImage(currentIndex);
            lightbox.classList.add('lightbox--active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            lightbox.classList.remove('lightbox--active');
            document.body.style.overflow = '';
        }

        function showImage(idx) {
            if (idx < 0 || idx >= images.length) return;
            currentIndex = idx;
            imgEl.src = images[idx].src;
            imgEl.alt = images[idx].alt;
            if (counterEl) {
                counterEl.textContent = (idx + 1) + ' / ' + images.length;
            }
        }

        function showPrev() {
            showImage((currentIndex - 1 + images.length) % images.length);
        }

        function showNext() {
            showImage((currentIndex + 1) % images.length);
        }

        if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
        if (prevBtn) prevBtn.addEventListener('click', showPrev);
        if (nextBtn) nextBtn.addEventListener('click', showNext);

        // Cerrar al hacer clic fuera de la imagen
        lightbox.addEventListener('click', function (e) {
            if (e.target === lightbox) closeLightbox();
        });

        // Navegacion con teclado
        document.addEventListener('keydown', function (e) {
            if (!lightbox.classList.contains('lightbox--active')) return;

            if (e.key === 'Escape') closeLightbox();
            else if (e.key === 'ArrowLeft') showPrev();
            else if (e.key === 'ArrowRight') showNext();
        });
    })();

    /* ======================================================================
       15. Hero slider (banner principal del home)
       ====================================================================== */
    (function initHeroSlider() {
        var slider = document.querySelector('.hero-slider');
        if (!slider) return;

        var slides = slider.querySelectorAll('.hero-slide');
        var dots = document.querySelectorAll('.hero-dot');
        var prevBtn = document.querySelector('.hero-control--prev');
        var nextBtn = document.querySelector('.hero-control--next');
        var current = 0;
        var total = slides.length;
        var interval = null;
        var INTERVAL_MS = 6000;

        if (total <= 1) return;

        function goTo(idx) {
            slides[current].classList.remove('hero-slide--active');
            if (dots[current]) dots[current].classList.remove('hero-dot--active');

            current = (idx + total) % total;

            slides[current].classList.add('hero-slide--active');
            if (dots[current]) dots[current].classList.add('hero-dot--active');
        }

        function startAuto() {
            stopAuto();
            interval = setInterval(function () {
                goTo(current + 1);
            }, INTERVAL_MS);
        }

        function stopAuto() {
            if (interval) clearInterval(interval);
        }

        if (prevBtn) prevBtn.addEventListener('click', function () { stopAuto(); goTo(current - 1); startAuto(); });
        if (nextBtn) nextBtn.addEventListener('click', function () { stopAuto(); goTo(current + 1); startAuto(); });

        dots.forEach(function (dot, idx) {
            dot.addEventListener('click', function () { stopAuto(); goTo(idx); startAuto(); });
        });

        startAuto();
    })();

    /* ======================================================================
       16. Service Worker (PWA)
       ====================================================================== */
    (function initServiceWorker() {
        if (!('serviceWorker' in navigator)) return;

        navigator.serviceWorker.register('/sw.js')
            .then(function (reg) {
                // SW registrado correctamente
                reg.addEventListener('updatefound', function () {
                    var newWorker = reg.installing;
                    if (!newWorker) return;
                    newWorker.addEventListener('statechange', function () {
                        if (newWorker.state === 'activated' && navigator.serviceWorker.controller) {
                            // Nueva versión disponible - mostrar aviso discreto
                            var toast = document.createElement('div');
                            toast.className = 'toast toast--info';
                            toast.innerHTML = '<span>Nueva versión disponible.</span> <a href="#" onclick="window.location.reload();return false;" style="color:#fff;font-weight:bold;margin-left:8px;">Actualizar</a>';
                            var container = document.querySelector('.toast-container');
                            if (container) {
                                container.appendChild(toast);
                                setTimeout(function () { toast.remove(); }, 10000);
                            }
                        }
                    });
                });
            })
            .catch(function () {
                // SW no soportado o error - silencioso
            });
    })();

    /* ======================================================================
       17. Countdown Timer (Hero)
       ====================================================================== */
    (function initCountdown() {
        var el = document.getElementById('heroCountdown');
        if (!el) return;

        var target = el.getAttribute('data-target');
        if (!target) return;

        // Parsing robusto: YYYY-MM-DD -> componentes individuales (evita problemas de timezone en Safari)
        var parts = target.split('-');
        if (parts.length !== 3) return;
        var targetDate = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10), 0, 0, 0);

        var diasEl = document.getElementById('cdDias');
        var horasEl = document.getElementById('cdHoras');
        var minEl = document.getElementById('cdMin');
        var segEl = document.getElementById('cdSeg');

        if (!diasEl || !horasEl || !minEl || !segEl) return;

        function pad(n) { return n < 10 ? '0' + n : String(n); }

        var interval;

        function update() {
            var now = new Date();
            var diff = targetDate - now;

            if (diff <= 0) {
                diasEl.textContent = '00';
                horasEl.textContent = '00';
                minEl.textContent = '00';
                segEl.textContent = '00';
                if (interval) clearInterval(interval);
                return;
            }

            var days = Math.floor(diff / (1000 * 60 * 60 * 24));
            var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            var secs = Math.floor((diff % (1000 * 60)) / 1000);

            diasEl.textContent = pad(days);
            horasEl.textContent = pad(hours);
            minEl.textContent = pad(mins);
            segEl.textContent = pad(secs);
        }

        update();
        interval = setInterval(update, 1000);
    })();

    /* ======================================================================
       18. Floating Share Button
       ====================================================================== */
    (function initShareFloat() {
        var container = document.getElementById('shareFloat');
        var btn = document.getElementById('shareFloatBtn');
        if (!container || !btn) return;

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            container.classList.toggle('share-float--open');
        });

        document.addEventListener('click', function (e) {
            if (!container.contains(e.target)) {
                container.classList.remove('share-float--open');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                container.classList.remove('share-float--open');
            }
        });
    })();

    /* ======================================================================
       19. PWA Install Banner
       ====================================================================== */
    (function initInstallBanner() {
        var deferredPrompt = null;

        window.addEventListener('beforeinstallprompt', function (e) {
            e.preventDefault();
            deferredPrompt = e;

            // Mostrar banner de instalación si no fue descartado antes
            if (localStorage.getItem('pwa-install-dismissed')) return;

            var banner = document.createElement('div');
            banner.className = 'pwa-install-banner';
            banner.innerHTML =
                '<div class="pwa-install-banner__content">' +
                '<span>Instala <strong>Regalos Purranque</strong> en tu dispositivo</span>' +
                '<div class="pwa-install-banner__actions">' +
                '<button class="btn btn--primary btn--sm" id="pwaInstallBtn">Instalar</button>' +
                '<button class="btn btn--outline btn--sm" id="pwaInstallDismiss">No ahora</button>' +
                '</div>' +
                '</div>';

            banner.style.cssText = 'position:fixed;bottom:0;left:0;right:0;background:#1e293b;color:#fff;padding:12px 16px;z-index:9999;box-shadow:0 -2px 10px rgba(0,0,0,.15);';
            banner.querySelector('.pwa-install-banner__content').style.cssText = 'max-width:600px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;';
            banner.querySelector('.pwa-install-banner__actions').style.cssText = 'display:flex;gap:8px;';

            document.body.appendChild(banner);

            document.getElementById('pwaInstallBtn').addEventListener('click', function () {
                if (deferredPrompt) {
                    deferredPrompt.prompt();
                    deferredPrompt.userChoice.then(function () {
                        deferredPrompt = null;
                        banner.remove();
                    });
                }
            });

            document.getElementById('pwaInstallDismiss').addEventListener('click', function () {
                banner.remove();
                localStorage.setItem('pwa-install-dismissed', '1');
            });
        });
    })();

});
