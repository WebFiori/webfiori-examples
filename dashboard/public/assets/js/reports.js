document.getElementById('gen-report').addEventListener('click', function() {
    var btn = this;
    var baseUrl = btn.dataset.baseUrl;
    var statusEl = document.getElementById('gen-status');
    btn.disabled = true;
    btn.textContent = 'Generating...';

    fetch(baseUrl + '/apis/reports', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'service=reports'
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.type === 'error') {
            statusEl.style.color = 'red';
            statusEl.textContent = data.message;
        } else {
            statusEl.style.color = 'green';
            statusEl.textContent = 'Report generated! Refreshing...';
            setTimeout(function() { location.reload(); }, 1000);
        }
        statusEl.style.display = 'block';
        btn.disabled = false;
        btn.textContent = 'Generate Report';
    });
});
