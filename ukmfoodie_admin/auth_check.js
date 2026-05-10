if (localStorage.getItem('admin_logged_in') !== 'true') {
    window.location.href = '../ukmfoodie_seller/login.html';
}

function logoutAdmin() {
    localStorage.removeItem('admin_logged_in');
    window.location.href = '../ukmfoodie_seller/login.html?logout=true';
}
