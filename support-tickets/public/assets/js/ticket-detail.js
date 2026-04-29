document.getElementById('reply-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var baseUrl = form.dataset.baseUrl;
    var ticketId = form.dataset.ticketId;
    var name = document.getElementById('authorName').value;
    var content = document.getElementById('replyContent').value;

    fetch(baseUrl + '/apis/replies', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'service=replies&ticketId=' + ticketId + '&authorName=' + encodeURIComponent(name) + '&content=' + encodeURIComponent(content)
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.type === 'error') return;
        var div = document.createElement('div');
        div.style = 'border-left:3px solid #2980b9;padding:0.5rem 1rem;margin:0.5rem 0';
        div.innerHTML = '<strong>' + name + '</strong><small> — just now</small><p>' + content + '</p>';
        form.parentNode.insertBefore(div, form);
        form.reset();
    });
});
