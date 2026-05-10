(function() {
    const stallId = localStorage.getItem('stall_id');
    const currentPage = window.location.pathname.split('/').pop();

    if (!stallId && currentPage !== 'login.html') {
        window.location.href = 'login.html';
    }
})();

function logoutSeller() {
    localStorage.clear();
    window.location.href = 'login.html?logout=true';
}
