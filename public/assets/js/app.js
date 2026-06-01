/**
 * =========================================================
 * Sistem Order - Main JavaScript
 * Modal system, toast notifications, cart, search, filters
 * =========================================================
 */

// =========================================================
// TOAST NOTIFICATION SYSTEM
// =========================================================
const SOToast = {
    _icons: {
        success: '✅',
        error:   '❌',
        warning: '⚠️',
        info:    'ℹ️',
    },
    _titles: {
        success: 'Berjaya',
        error:   'Ralat',
        warning: 'Amaran',
        info:    'Maklumat',
    },

    show(message, type = 'info', duration = 3500) {
        const container = document.getElementById('soToastContainer');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = `so-toast so-${type}`;
        toast.innerHTML = `
            <span class="so-toast-icon">${this._icons[type] || 'ℹ️'}</span>
            <div class="so-toast-body">
                <div class="so-toast-title">${this._titles[type] || 'Notifikasi'}</div>
                <div class="so-toast-msg">${message}</div>
            </div>
            <button class="so-toast-close" aria-label="Tutup">&times;</button>
            <div class="so-toast-progress" style="animation-duration:${duration}ms;"></div>
        `;

        container.appendChild(toast);

        // Close button
        toast.querySelector('.so-toast-close').addEventListener('click', () => this._dismiss(toast));

        // Auto dismiss
        const timer = setTimeout(() => this._dismiss(toast), duration);
        toast._timer = timer;

        return toast;
    },

    _dismiss(toast) {
        if (!toast || toast._dismissed) return;
        toast._dismissed = true;
        clearTimeout(toast._timer);
        toast.classList.add('so-toast-out');
        toast.addEventListener('animationend', () => toast.remove(), { once: true });
    },

    success(msg, duration) { return this.show(msg, 'success', duration); },
    error(msg, duration)   { return this.show(msg, 'error',   duration); },
    warning(msg, duration) { return this.show(msg, 'warning', duration); },
    info(msg, duration)    { return this.show(msg, 'info',    duration); },
};

// =========================================================
// MODAL CONFIRM SYSTEM
// =========================================================
const SOModal = {
    _confirmResolve: null,

    confirm({ title = 'Adakah anda pasti?', message = 'Tindakan ini tidak boleh dibatalkan.', icon = '⚠️', okText = 'Ya, Teruskan', okClass = 'btn-danger', cancelText = 'Batal' } = {}) {
        return new Promise(resolve => {
            this._confirmResolve = resolve;

            document.getElementById('soConfirmIcon').textContent    = icon;
            document.getElementById('soConfirmTitle').textContent   = title;
            document.getElementById('soConfirmMessage').textContent = message;

            const okBtn = document.getElementById('soConfirmOk');
            okBtn.textContent = okText;
            okBtn.className   = `btn ${okClass}`;

            document.getElementById('soConfirmCancel').textContent = cancelText;

            const modal = document.getElementById('soConfirmModal');
            modal.className = 'so-modal so-confirm';

            this._open('soConfirmOverlay');
        });
    },

    alert({ title = 'Notifikasi', message = '', icon = 'ℹ️', type = 'info', okText = 'OK' } = {}) {
        return new Promise(resolve => {
            document.getElementById('soAlertIcon').textContent    = icon;
            document.getElementById('soAlertTitle').textContent   = title;
            document.getElementById('soAlertMessage').textContent = message;

            const okBtn = document.getElementById('soAlertOk');
            okBtn.textContent = okText;

            const typeMap = { success: 'btn-success', error: 'btn-danger', warning: 'btn-warning', info: 'btn-primary' };
            okBtn.className = `btn ${typeMap[type] || 'btn-primary'}`;

            const modal = document.getElementById('soAlertModal');
            modal.className = `so-modal so-${type}`;

            this._open('soAlertOverlay');

            document.getElementById('soAlertOk')._resolve = resolve;
        });
    },

    _open(overlayId) {
        const overlay = document.getElementById(overlayId);
        if (!overlay) return;
        overlay.classList.add('so-open');
        // Focus first button for accessibility
        setTimeout(() => {
            const btn = overlay.querySelector('.so-modal-actions .btn');
            if (btn) btn.focus();
        }, 100);
    },

    _close(overlayId) {
        const overlay = document.getElementById(overlayId);
        if (overlay) overlay.classList.remove('so-open');
    },
};

// Wire up modal buttons - use event delegation on document so it works
// regardless of when footer.php injects the modal HTML
document.addEventListener('click', function (e) {
    // Confirm OK
    if (e.target.id === 'soConfirmOk') {
        SOModal._close('soConfirmOverlay');
        if (SOModal._confirmResolve) { SOModal._confirmResolve(true); SOModal._confirmResolve = null; }
        return;
    }
    // Confirm Cancel
    if (e.target.id === 'soConfirmCancel') {
        SOModal._close('soConfirmOverlay');
        if (SOModal._confirmResolve) { SOModal._confirmResolve(false); SOModal._confirmResolve = null; }
        return;
    }
    // Alert OK
    if (e.target.id === 'soAlertOk') {
        SOModal._close('soAlertOverlay');
        const btn = document.getElementById('soAlertOk');
        if (btn && btn._resolve) { btn._resolve(); btn._resolve = null; }
        return;
    }
    // Backdrop click
    if (e.target.id === 'soConfirmOverlay') {
        SOModal._close('soConfirmOverlay');
        if (SOModal._confirmResolve) { SOModal._confirmResolve(false); SOModal._confirmResolve = null; }
        return;
    }
    if (e.target.id === 'soAlertOverlay') {
        SOModal._close('soAlertOverlay');
        return;
    }
});

// Close on Escape
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        if (document.getElementById('soConfirmOverlay')?.classList.contains('so-open')) {
            SOModal._close('soConfirmOverlay');
            if (SOModal._confirmResolve) { SOModal._confirmResolve(false); SOModal._confirmResolve = null; }
        }
        if (document.getElementById('soAlertOverlay')?.classList.contains('so-open')) {
            SOModal._close('soAlertOverlay');
        }
    }
});

// =========================================================
// LEGACY SHIM - keep old showToast() working
// =========================================================
function showToast(message, duration) {
    SOToast.info(message, duration || 3000);
}

// =========================================================
// LOADING OVERLAY
// =========================================================
function showLoading(msg) {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        if (msg) overlay.querySelector('.loading-text').textContent = msg;
        overlay.classList.add('active');
    }
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.classList.remove('active');
}

// =========================================================
// CART - Add to Cart
// =========================================================
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-add-cart');
    if (!btn) return;
    e.preventDefault();
    const id   = btn.dataset.id;
    const nama = btn.dataset.nama;

    fetch(getBaseUrl() + '/index.php?page=api-cart-add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'item_id=' + encodeURIComponent(id),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            SOToast.success(nama + ' ditambah ke troli!');
            updateCartBadge(data.cartCount);
        } else {
            SOToast.error(data.message || 'Ralat menambah item');
        }
    })
    .catch(() => SOToast.error('Ralat sambungan. Sila cuba lagi.'));
});

function updateCartBadge(count) {
    let badge = document.getElementById('cartBadge');
    if (count > 0) {
        if (!badge) {
            const cartLink = document.querySelector('a[href*="page=cart"]');
            if (cartLink) {
                badge = document.createElement('span');
                badge.className = 'cart-badge';
                badge.id = 'cartBadge';
                cartLink.appendChild(badge);
            }
        }
        if (badge) badge.textContent = count;
    } else if (badge) {
        badge.remove();
    }
}

// =========================================================
// CART - Quantity & Remove (uses modal confirm)
// =========================================================
document.addEventListener('click', async function (e) {
    // Quantity buttons
    const qtyBtn = e.target.closest('.btn-qty');
    if (qtyBtn) {
        e.preventDefault();
        const id     = qtyBtn.dataset.id;
        const action = qtyBtn.dataset.action;

        showLoading('Mengemaskini...');
        fetch(getBaseUrl() + '/index.php?page=api-cart-update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'item_id=' + id + '&action=' + action,
        })
        .then(r => r.json())
        .then(data => {
            hideLoading();
            if (data.success) location.reload();
        })
        .catch(() => hideLoading());
    }

    // Remove from cart - modal confirm
    const removeBtn = e.target.closest('.btn-remove-cart');
    if (removeBtn) {
        e.preventDefault();
        const confirmed = await SOModal.confirm({
            title:     'Buang Item?',
            message:   'Adakah anda pasti ingin membuang item ini dari troli?',
            icon:      '🗑️',
            okText:    'Ya, Buang',
            okClass:   'btn-danger',
            cancelText:'Batal',
        });
        if (!confirmed) return;

        const id = removeBtn.dataset.id;
        showLoading('Membuang...');
        fetch(getBaseUrl() + '/index.php?page=api-cart-remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'item_id=' + id,
        })
        .then(r => r.json())
        .then(data => {
            hideLoading();
            if (data.success) location.reload();
        })
        .catch(() => hideLoading());
    }
});

// =========================================================
// MENU SEARCH & FILTER
// =========================================================
const searchInput   = document.getElementById('searchMenu');
const clearSearchBtn = document.getElementById('clearSearch');

if (searchInput) {
    searchInput.addEventListener('input', function () {
        const query = this.value.toLowerCase().trim();
        if (clearSearchBtn) {
            clearSearchBtn.style.display = query.length > 0 ? 'block' : 'none';
        }
        document.querySelectorAll('.menu-item').forEach(card => {
            const name = card.dataset.name || '';
            card.style.display = name.includes(query) ? '' : 'none';
        });
    });

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function () {
            searchInput.value = '';
            clearSearchBtn.style.display = 'none';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
    }
}

// Category tabs
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function () {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const cat = this.dataset.category;

        document.querySelectorAll('.category-section').forEach(section => {
            section.style.display = (cat === 'all' || section.dataset.catId === cat) ? '' : 'none';
        });

        document.querySelectorAll('.menu-item').forEach(card => {
            card.style.display = (cat === 'all' || card.dataset.category === cat) ? '' : 'none';
        });
    });
});

// =========================================================
// FORMS - show loading on submit
// =========================================================
document.querySelectorAll('form[method="POST"]').forEach(form => {
    form.addEventListener('submit', function () {
        showLoading('Memproses...');
    });
});

// =========================================================
// UTILITY
// =========================================================
function getBaseUrl() {
    const url = window.location.href;
    const idx = url.indexOf('/index.php');
    return idx > -1 ? url.substring(0, idx) : '';
}
