const API_BASE_GLOBAL = 'http://localhost/ukmfoodie_workspace/ukmfoodie_api';
const notificationSoundGlobal = new Audio('notification.mp3');

// Inject CSS if not already present
if (!document.querySelector('link[href*="notifications.css"]')) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'notifications.css?v=' + new Date().getTime(); // Anti-cache
    document.head.appendChild(link);
}

function showToast(title, message, type = 'success') {
    let toastContainer = document.getElementById('web-toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'web-toast-container';
        toastContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 10000; display: flex; flex-direction: column; gap: 10px;';
        document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.className = `web-toast ${type}`;
    toast.style.position = 'relative'; // Override fixed from CSS if it was there
    toast.style.top = 'auto';
    toast.style.right = 'auto';
    toast.style.transform = 'translateX(120%)';
    
    const icon = type === 'success' ? 'fa-circle-check' : type === 'error' ? 'fa-circle-xmark' : 'fa-circle-info';
    
    toast.innerHTML = `
        <i class="fa-solid ${icon} web-toast-icon"></i>
        <div class="web-toast-content">
            <span class="web-toast-title">${title}</span>
            <span class="web-toast-message">${message}</span>
        </div>
    `;

    toastContainer.appendChild(toast);

    // Trigger animation
    setTimeout(() => {
        toast.classList.add('show');
        toast.style.transform = 'translateX(0)';
    }, 10);

    // Remove after 4s
    setTimeout(() => {
        toast.classList.remove('show');
        toast.style.transform = 'translateX(120%)';
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

function showConfirm(title, message, onConfirm) {
    const overlay = document.createElement('div');
    overlay.className = 'confirm-overlay';
    
    overlay.innerHTML = `
        <div class="confirm-modal">
            <div class="confirm-icon">
                <i class="fa-solid fa-circle-question"></i>
            </div>
            <div class="confirm-title">${title}</div>
            <div class="confirm-message">${message}</div>
            <div class="confirm-actions">
                <button class="confirm-btn confirm-btn-cancel" id="confirm-cancel">Cancel</button>
                <button class="confirm-btn confirm-btn-confirm" id="confirm-ok">Confirm</button>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);

    // Animate in
    setTimeout(() => overlay.classList.add('show'), 10);

    const closeModal = () => {
        overlay.classList.remove('show');
        setTimeout(() => overlay.remove(), 300);
    };

    overlay.querySelector('#confirm-cancel').onclick = closeModal;
    overlay.querySelector('#confirm-ok').onclick = () => {
        onConfirm();
        closeModal();
    };

    // Close on overlay click
    overlay.onclick = (e) => {
        if (e.target === overlay) closeModal();
    };
}


async function checkGlobalOrders() {
    try {
        const stall_id = localStorage.getItem('stall_id');
        if (!stall_id) return; // Jangan check kalau belum login

        const response = await fetch(`${API_BASE_GLOBAL}/fetch_orders.php?stall_id=${stall_id}`);
        const result = await response.json();
        
        if(result.status === 'success') {
            const currentOrders = result.data;
            const pendingOrders = currentOrders.filter(o => o.status === 'Pending');
            const badge = document.getElementById('orderBadge');
            
            if (badge) {
                badge.innerText = pendingOrders.length;
                badge.style.display = pendingOrders.length > 0 ? 'inline-block' : 'none';
            }

            // Gunakan ID pesanan untuk semakan yang lebih tepat
            let seenIds = JSON.parse(localStorage.getItem('seenPendingOrderIds')) || [];
            let hasNewOrder = false;

            pendingOrders.forEach(order => {
                // Jika ID pesanan ini belum pernah dilihat sebelum ini
                if (!seenIds.includes(order.id)) {
                    hasNewOrder = true;
                }
            });

            if (hasNewOrder) {
                notificationSoundGlobal.currentTime = 0; // Reset bunyi ke awal
                notificationSoundGlobal.play().catch(e => {
                    console.log("Audio blocked by browser. Please click anywhere on the screen once to enable sound.", e);
                });
            }
            
            // Simpan semua ID pending sekarang untuk rujukan akan datang
            const currentPendingIds = pendingOrders.map(o => o.id);
            localStorage.setItem('seenPendingOrderIds', JSON.stringify(currentPendingIds));

            const event = new CustomEvent('globalOrdersUpdated', { detail: currentOrders });
            document.dispatchEvent(event);
        }
    } catch (error) { 
        console.error("Global Order Error:", error); 
    }
}

// Tukar kepada 3 saat (3000ms) untuk lebih laju
setInterval(checkGlobalOrders, 3000);
if (document.readyState === 'loading') {
    window.addEventListener('DOMContentLoaded', checkGlobalOrders);
} else {
    checkGlobalOrders();
}
