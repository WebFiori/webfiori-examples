document.getElementById('comment-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var baseUrl = form.dataset.baseUrl;
    var name = document.getElementById('authorName').value;
    var email = document.getElementById('authorEmail').value;
    var content = document.getElementById('commentContent').value;
    var postId = document.getElementById('postId').value;
    fetch(baseUrl + '/apis/comments', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'service=comments&postId=' + postId + '&authorName=' + encodeURIComponent(name) + '&authorEmail=' + encodeURIComponent(email) + '&content=' + encodeURIComponent(content)
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.type === 'error') return;
        var div = document.createElement('div');
        div.className = 'comment';
        div.innerHTML = '<span class="comment-author">' + name + '</span>' +
            '<span class="comment-date"> — just now</span>' +
            '<p>' + content + '</p>';
        var heading = document.querySelector('.comments-section h3');
        heading.parentNode.insertBefore(div, heading.nextSibling);
        form.reset();
    });
});
