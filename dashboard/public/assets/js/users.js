document.getElementById('add-user').addEventListener('submit', function(e) {
    e.preventDefault();
    var baseUrl = this.dataset.baseUrl;

    fetch(baseUrl + '/apis/users', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'service=users' +
            '&name=' + encodeURIComponent(document.getElementById('userName').value) +
            '&email=' + encodeURIComponent(document.getElementById('userEmail').value) +
            '&password=' + encodeURIComponent(document.getElementById('userPassword').value) +
            '&role=' + encodeURIComponent(document.getElementById('userRole').value)
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.type === 'error') {
            alert(data.message);
        } else {
            location.reload();
        }
    });
});
