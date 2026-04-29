document.getElementById('shorten-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var baseUrl = this.dataset.baseUrl;
    var url = document.getElementById('url').value;
    var resultEl = document.getElementById('result');
    resultEl.style.display = 'none';

    fetch(baseUrl + '/apis/links', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'service=links&url=' + encodeURIComponent(url)
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.type === 'error') {
            resultEl.textContent = data.message;
            resultEl.style.color = 'red';
        } else {
            var code = data.data[0].id;
            var shortUrl = baseUrl + '/' + code;
            resultEl.innerHTML = 'Short URL: <a href="' + shortUrl + '">' + shortUrl + '</a>';
            resultEl.style.color = 'green';
        }
        resultEl.style.display = 'block';
    });
});
