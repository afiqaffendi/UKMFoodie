(function() {
    function checkAdminAuth() {
        const isAdmin = localStorage.getItem('admin_logged_in');
        if (isAdmin !== 'true') {
            window.location.replace('../ukmfoodie_seller/login.html');
        }
    }

    checkAdminAuth();

    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });
})();

function logoutAdmin() {
    showConfirm(
        "Logout Confirmation",
        "Are you sure you want to log out from the Admin Portal?",
        () => {
            localStorage.removeItem('admin_logged_in');
            window.location.replace('../ukmfoodie_seller/login.html?logout=true');
        }
    );
}
