document.getElementById('create-project').addEventListener('submit', function(e) {
    e.preventDefault();
    var baseUrl = this.dataset.baseUrl;
    var name = document.getElementById('projName').value;
    var desc = document.getElementById('projDesc').value;

    fetch(baseUrl + '/apis/projects', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'service=projects&name=' + encodeURIComponent(name) + '&description=' + encodeURIComponent(desc)
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.type === 'error') {
            alert(data.message);
        } else {
            location.reload();
        }
    });
});
