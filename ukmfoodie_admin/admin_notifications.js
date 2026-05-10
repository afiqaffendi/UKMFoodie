const API_BASE = window.location.origin + '/ukmfoodie_workspace/ukmfoodie_api';

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
