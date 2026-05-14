(function() {
    function checkAuth() {
        const stallId = localStorage.getItem('stall_id');
        const path = window.location.pathname;
        const currentPage = path.split('/').pop() || 'index.html';

        // Jika belum login dan bukan di halaman login/register, hantar ke login
        if (!stallId && currentPage !== 'login.html' && currentPage !== 'register.html') {
            window.location.replace('login.html');
        }

        // Jika sudah login tapi cuba buka halaman login/register, hantar ke dashboard
        if (stallId && (currentPage === 'login.html' || currentPage === 'register.html')) {
            window.location.replace('dashboard.html');
        }
    }

    // Jalankan semakan bila script dimuatkan
    checkAuth();

    // Paksa semakan semula jika pengguna tekan butang 'Back' (mengatasi cache browser)
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
})();

function logoutSeller() {
    showConfirm("Logout Confirmation", "Are you sure you want to log out from your stall portal?", () => {
        localStorage.clear();
        // Gunakan replace supaya halaman semasa dipadam dari history
        window.location.replace('login.html?logout=true');
    });
}
