import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import Swal from 'sweetalert2';

const SwalThemed = Swal.mixin({
    background: '#2d3338',
    color: '#e9ecef',
    confirmButtonColor: '#0d6efd',
    cancelButtonColor: '#6c757d',
    iconColor: '#e9ecef',
});

window.Swal = SwalThemed;

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function escapeHtml(s) {
    const d = document.createElement('div');
    d.textContent = s;
    return d.innerHTML;
}

function initApp() {
    const menuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebarOffcanvas');
    const backdrop = document.getElementById('sidebarBackdrop');

    if (menuBtn && sidebar) {
        function openSidebar() {
            sidebar.classList.add('show', 'offcanvas-mobile-open');
            sidebar.setAttribute('aria-hidden', 'false');
            if (backdrop) backdrop.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            sidebar.classList.remove('show', 'offcanvas-mobile-open');
            sidebar.setAttribute('aria-hidden', 'true');
            if (backdrop) backdrop.classList.remove('show');
            document.body.style.overflow = '';
        }
        function toggleSidebar() {
            if (sidebar.classList.contains('show')) closeSidebar();
            else openSidebar();
        }
        menuBtn.addEventListener('click', function (e) {
            e.preventDefault();
            toggleSidebar();
        });
        if (backdrop) backdrop.addEventListener('click', closeSidebar);
        const closeEls = sidebar.querySelectorAll('.offcanvas-body a[href], .offcanvas-header [data-bs-dismiss], .offcanvas-header .btn-close');
        closeEls.forEach(function (el) {
            el.addEventListener('click', closeSidebar);
        });
    }

    document.addEventListener(
        'submit',
        function (e) {
            const form = e.target;
            if (form.tagName !== 'FORM' || !form.hasAttribute('data-confirm')) return;
            e.preventDefault();
            const msg = form.getAttribute('data-confirm') || 'Подтвердить?';
            SwalThemed.fire({
                title: 'Подтверждение',
                text: msg,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Да',
                cancelButtonText: 'Отмена',
                focusCancel: true,
            }).then(function (r) {
                if (r.isConfirmed) {
                    form.removeAttribute('data-confirm');
                    form.submit();
                }
            });
        },
        true
    );

    document.querySelectorAll('form[data-loading-on-submit]').forEach(function (form) {
        form.addEventListener('submit', function () {
            form.setAttribute('aria-busy', 'true');
            const btn = form.querySelector('[type="submit"]');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                btn.dataset._prevHtml = btn.innerHTML;
                btn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> ' +
                    (btn.getAttribute('data-loading-label') || 'Отправка…');
            }
        });
    });

    document.querySelectorAll('[data-flash="success"], [data-flash="error"]').forEach(function (el) {
        const isSuccess = el.getAttribute('data-flash') === 'success';
        const text = (el.textContent || '').trim();
        const hint = (el.getAttribute('data-flash-hint') || '').trim();
        const raw = (el.getAttribute('data-flash-raw') || '').trim();
        const ariaLive = document.getElementById('aria-flash-live');
        if (text && ariaLive) {
            ariaLive.textContent = isSuccess ? 'Готово: ' + text : 'Ошибка: ' + text;
        }
        if (text) {
            let html = '<p class="mb-0 text-start">' + escapeHtml(text) + '</p>';
            if (hint) {
                html +=
                    '<p class="small text-start mt-2 mb-0 opacity-90">' + escapeHtml(hint) + '</p>';
            }
            if (raw && raw.length > 0 && raw !== text) {
                html +=
                    '<details class="text-start mt-2 mb-0 small"><summary>Технические детали</summary><pre class="mt-1 mb-0 text-start small overflow-auto" style="max-height:12rem;">' +
                    escapeHtml(raw) +
                    '</pre></details>';
            }
            SwalThemed.fire({
                icon: isSuccess ? 'success' : 'error',
                title: isSuccess ? 'Готово' : 'Ошибка',
                html: html,
                confirmButtonText: 'OK',
                width: 'min(32rem, 100vw)',
            });
            el.remove();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initApp);
} else {
    initApp();
}
