/**
 * =========================================================
 * Sistem Order - Main JavaScript
 * =========================================================
 */

// Show loading overlay
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

// Toast notification
function showToast(message, duration = 2500) {
    const toast = document.getElementById('toast');
    if (!toast) return;
    document.getElementById('toastText').textContent = message;
    toast.style.display = 'block';
    setTimeout(() => { toast.style.display = 'none'; }, duration);
}

// =========================================================
// Menu Page - Add to Cart
// =========================================================
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-add-cart');
    if (!btn) return;
    e.preventDefault();
    const id = btn.dataset.id;
    const nama = btn.dataset.nama;

    fetch(getBaseUrl() + '/index.php?page=api-cart-add', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'item_id=' + encodeURIComponent(id)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(nama + ' ditambah ke cart!');
            updateCartBadge(data.cartCount);
        } else {
            showToast(data.message || 'Ralat menambah item');
        }
    })
    .catch(() => showToast('Ralat sambungan'));
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
// Menu Search & Filter
// =========================================================
const searchInput = document.getElementById('searchMenu');
const clearSearchBtn = document.getElementById('clearSearch');

if (searchInput) {
    searchInput.addEventListener('input', function() {
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
        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            clearSearchBtn.style.display = 'none';
            // Trigger input event to reset filter
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
    }
}

// Category tabs
document.querySelectorAll('.category-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.category-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const cat = this.dataset.category;
        
        document.querySelectorAll('.category-section').forEach(section => {
            if (cat === 'all') {
                section.style.display = '';
            } else {
                section.style.display = section.dataset.catId === cat ? '' : 'none';
            }
        });

        document.querySelectorAll('.menu-item').forEach(card => {
            if (cat === 'all') {
                card.style.display = '';
            } else {
                card.style.display = card.dataset.category === cat ? '' : 'none';
            }
        });
    });
});

// =========================================================
// Cart - Quantity & Remove
// =========================================================
document.addEventListener('click', function(e) {
    const qtyBtn = e.target.closest('.btn-qty');
    if (qtyBtn) {
        e.preventDefault();
        const id = qtyBtn.dataset.id;
        const action = qtyBtn.dataset.action;
        
        showLoading('Mengemaskini...');
        fetch(getBaseUrl() + '/index.php?page=api-cart-update', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'item_id=' + id + '&action=' + action
        })
        .then(r => r.json())
        .then(data => {
            hideLoading();
            if (data.success) location.reload();
        })
        .catch(() => { hideLoading(); });
    }

    const removeBtn = e.target.closest('.btn-remove-cart');
    if (removeBtn) {
        e.preventDefault();
        if (!confirm('Buang item ini dari cart?')) return;
        const id = removeBtn.dataset.id;
        
        showLoading('Membuang...');
        fetch(getBaseUrl() + '/index.php?page=api-cart-remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'item_id=' + id
        })
        .then(r => r.json())
        .then(data => {
            hideLoading();
            if (data.success) location.reload();
        })
        .catch(() => { hideLoading(); });
    }
});

// =========================================================
// Forms with loading
// =========================================================
document.querySelectorAll('form[method="POST"]').forEach(form => {
    form.addEventListener('submit', function() {
        showLoading('Memproses...');
    });
});

// =========================================================
// Utility
// =========================================================
function getBaseUrl() {
    // Extract base URL from current page
    const url = window.location.href;
    const idx = url.indexOf('/index.php');
    return idx > -1 ? url.substring(0, idx) : '';
}

// Close modals on overlay click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.classList.remove('active');
    }
});

// Close modals with Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
    }
});
