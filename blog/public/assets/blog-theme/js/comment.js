document.getElementById('comment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var baseUrl = form.dataset.baseUrl;
    var name = document.getElementById('authorName').value;
    var email = document.getElementById('authorEmail').value;
    var content = document.getElementById('commentContent').value;
    var postId = document.getElementById('postId').value;
    var btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    fetch(baseUrl + '/apis/comments', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'service=comments&postId=' + postId + '&authorName=' + encodeURIComponent(name) + '&authorEmail=' + encodeURIComponent(email) + '&content=' + encodeURIComponent(content)
    }).then(function(r) {
        if (r.status === 429) {
            showFormMessage(form, 'Too many requests. Please wait a moment before trying again.', 'error');
            return null;
        }
        return r.json();
    }).then(function(data) {
        if (!data) return;
        if (data.type === 'error') {
            showFormMessage(form, data.message, 'error');
            return;
        }
        var div = document.createElement('div');
        div.className = 'comment';
        div.innerHTML = '<span class="comment-author">' + escapeHtml(name) + '</span>' +
            '<span class="comment-date"> — just now</span>' +
            '<p>' + escapeHtml(content) + '</p>';
        var heading = document.querySelector('.comments-section h3');
        heading.parentNode.insertBefore(div, heading.nextSibling);
        form.reset();
        showFormMessage(form, 'Comment posted successfully!', 'success');
    }).catch(function() {
        showFormMessage(form, 'An error occurred. Please try again.', 'error');
    }).finally(function() {
        btn.disabled = false;
    });
});

function showFormMessage(form, message, type) {
    var existing = form.querySelector('.form-message');
    if (existing) existing.remove();
    var msg = document.createElement('div');
    msg.className = 'form-message form-message--' + type;
    msg.setAttribute('role', 'alert');
    msg.textContent = message;
    form.prepend(msg);
    setTimeout(function() { msg.remove(); }, 5000);
}

function escapeHtml(text) {
    var d = document.createElement('div');
    d.textContent = text;
    return d.innerHTML;
}
