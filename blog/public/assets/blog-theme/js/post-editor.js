document.getElementById('post-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var baseUrl = form.dataset.baseUrl;
    var method = form.dataset.method;
    var params = 'service=posts' +
        '&title=' + encodeURIComponent(document.getElementById('title').value) +
        '&slug=' + encodeURIComponent(document.getElementById('slug').value) +
        '&content=' + encodeURIComponent(document.getElementById('content').value) +
        '&status=' + encodeURIComponent(document.getElementById('status').value);
    var catId = document.getElementById('categoryId').value;
    if (catId) params += '&categoryId=' + catId;
    var idEl = document.getElementById('postId');
    if (idEl) params += '&id=' + idEl.value;
    fetch(baseUrl + '/apis/posts', {
        method: method,
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: params
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.type === 'error') {
            alert(data.message);
        } else {
            window.location.href = baseUrl + '/admin';
        }
    });
});
