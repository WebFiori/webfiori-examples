document.getElementById('login-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var baseUrl = this.dataset.baseUrl;
    var errEl = document.getElementById('login-error');
    errEl.style.display = 'none';
    fetch(baseUrl + '/apis/auth', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'service=auth&email=' + encodeURIComponent(document.getElementById('email').value) + '&password=' + encodeURIComponent(document.getElementById('password').value)
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.type === 'error') {
            errEl.textContent = data.message;
            errEl.style.display = 'block';
        } else {
            window.location.href = baseUrl + '/dashboard';
        }
    });
});
