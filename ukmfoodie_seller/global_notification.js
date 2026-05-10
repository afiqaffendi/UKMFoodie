const API_BASE_GLOBAL = 'http://localhost/ukmfoodie_workspace/ukmfoodie_api';
const notificationSoundGlobal = new Audio('notification.mp3');

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
                    console.log("Audio diblokir oleh pelayar. Sila klik di mana-mana bahagian skrin sekali untuk aktifkan bunyi.", e);
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
