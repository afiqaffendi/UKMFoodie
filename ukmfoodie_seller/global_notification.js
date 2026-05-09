const API_BASE_GLOBAL = 'http://localhost/ukmfoodie_workspace/ukmfoodie_api';
const notificationSoundGlobal = new Audio('notification.mp3');

async function checkGlobalOrders() {
    try {
        const response = await fetch(`${API_BASE_GLOBAL}/fetch_orders.php?stall_id=1`);
        const result = await response.json();
        
        if(result.status === 'success') {
            const currentOrders = result.data;
            const badge = document.getElementById('orderBadge');
            
            if (badge) {
                badge.innerText = currentOrders.length;
                badge.style.display = currentOrders.length > 0 ? 'inline-block' : 'none';
            }

            let lastCount = parseInt(localStorage.getItem('lastPendingOrderCount')) || 0;
            
            if (currentOrders.length > lastCount) {
                notificationSoundGlobal.play().catch(e => console.log("Audio autoplay blocked by browser:", e));
            }
            
            localStorage.setItem('lastPendingOrderCount', currentOrders.length);

            const event = new CustomEvent('globalOrdersUpdated', { detail: currentOrders });
            document.dispatchEvent(event);
        }
    } catch (error) { 
        console.error("Global Order Error:", error); 
    }
}

setInterval(checkGlobalOrders, 10000);
if (document.readyState === 'loading') {
    window.addEventListener('DOMContentLoaded', checkGlobalOrders);
} else {
    checkGlobalOrders();
}
