import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import Swal from 'sweetalert2';

window.Swal = Swal;

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

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

    // Подтверждение форм через SweetAlert2 (делегирование — работает и для динамически добавленных форм)
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (form.tagName !== 'FORM' || !form.hasAttribute('data-confirm')) return;
        e.preventDefault();
        const msg = form.getAttribute('data-confirm') || 'Подтвердить?';
        Swal.fire({
            title: 'Подтверждение',
            text: msg,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Да',
            cancelButtonText: 'Отмена',
            focusCancel: true
        }).then(function (r) {
            if (r.isConfirmed) {
                form.removeAttribute('data-confirm');
                form.submit();
            }
        });
    }, true);

    document.querySelectorAll('[data-flash="success"], [data-flash="error"]').forEach(function (el) {
        const isSuccess = el.getAttribute('data-flash') === 'success';
        const text = (el.textContent || '').trim();
        if (text) {
            Swal.fire({
                icon: isSuccess ? 'success' : 'error',
                title: isSuccess ? 'Готово' : 'Ошибка',
                text: text,
                confirmButtonText: 'OK'
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
