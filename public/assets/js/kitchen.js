/**
 * =========================================================
 * Sistem Order - Kitchen Dashboard JS
 * Auto-refresh orders & handle status updates
 * =========================================================
 */

// Update order status
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.btn-update-status');
    if (!btn) return;
    e.preventDefault();

    const orderId = btn.dataset.id;
    const newStatus = btn.dataset.status;
    const statusLabels = {
        confirmed: 'Terima', preparing: 'Sediakan', ready: 'Siap', completed: 'Selesai', cancelled: 'Tolak'
    };

    if (newStatus === 'cancelled' && !confirm('Tolak pesanan ini?')) return;

    showLoading('Mengemaskini status...');
    
    const formData = new FormData();
    formData.append('order_id', orderId);
    formData.append('status', newStatus);

    fetch(getBaseUrl() + '/index.php?page=api-kitchen-update', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Ralat mengemaskini status');
        }
    })
    .catch(() => {
        hideLoading();
        alert('Ralat sambungan');
    });
});

// Auto-refresh kitchen orders every 10 seconds
let kitchenRefreshTimer;
function refreshOrders() {
    location.reload();
}

function startAutoRefresh() {
    kitchenRefreshTimer = setInterval(refreshOrders, 15000); // 15 seconds
}

// Start auto-refresh when page loads
startAutoRefresh();

// Pause auto-refresh when user is interacting
document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
        clearInterval(kitchenRefreshTimer);
    } else {
        startAutoRefresh();
    }
});

// Play notification sound for new pending orders
(function() {
    const pendingCount = document.querySelectorAll('.badge-pending').length;
    if (pendingCount > 0) {
        const stored = sessionStorage.getItem('lastPendingCount');
        if (stored && parseInt(stored) < pendingCount) {
            // New order detected
            try {
                if ('Notification' in window && Notification.permission === 'granted') {
                    new Notification('Pesanan Baru!', { body: pendingCount + ' pesanan menunggu', icon: '🍽️' });
                }
            } catch(e) {}
        }
        sessionStorage.setItem('lastPendingCount', pendingCount);
    }
})();

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}
