window.API_BASE = window.location.origin + '/ukmfoodie_workspace/ukmfoodie_api';
var API_BASE = window.API_BASE;

// Inject CSS
if (!document.querySelector('link[href*="admin_notifications.css"]')) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'admin_notifications.css?v=' + new Date().getTime();
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
    toast.style.position = 'relative';
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

    setTimeout(() => {
        toast.classList.add('show');
        toast.style.transform = 'translateX(0)';
    }, 10);

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

    setTimeout(() => overlay.classList.add('show'), 10);

    const closeModal = () => {
        overlay.classList.remove('show');
        setTimeout(() => overlay.remove(), 300);
    };

    overlay.querySelector('.confirm-btn-cancel').addEventListener('click', closeModal);
    overlay.querySelector('.confirm-btn-confirm').addEventListener('click', () => {
        onConfirm();
        closeModal();
    });

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeModal();
    });
}

async function updateAdminBadges() {
    try {
        const response = await fetch(`${API_BASE}/fetch_pending_stalls.php`);
        const result = await response.json();
        
        const badges = document.querySelectorAll('#pendingBadge');
        if (result.status === 'success') {
            const count = result.data.length;
            badges.forEach(badge => {
                if (count > 0) {
                    badge.innerText = count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            });
        }
    } catch (error) {
        console.error("Error polling admin notifications:", error);
    }
}

// Semak setiap 5 saat
setInterval(updateAdminBadges, 5000);
document.addEventListener('DOMContentLoaded', updateAdminBadges);
