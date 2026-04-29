document.getElementById('ticket-form').addEventListener('submit', function(e) {
    e.preventDefault();
    var form = this;
    var baseUrl = form.dataset.baseUrl;
    var errEl = document.getElementById('form-error');
    var successEl = document.getElementById('form-success');
    errEl.style.display = 'none';
    successEl.style.display = 'none';

    var formData = new FormData();
    formData.append('service', 'tickets');
    formData.append('submitterName', document.getElementById('submitterName').value);
    formData.append('submitterEmail', document.getElementById('submitterEmail').value);
    formData.append('subject', document.getElementById('subject').value);
    formData.append('description', document.getElementById('description').value);
    formData.append('priority', document.getElementById('priority').value);

    // Append files
    var fileInput = document.getElementById('file');
    for (var i = 0; i < fileInput.files.length; i++) {
        formData.append('file[]', fileInput.files[i]);
    }

    fetch(baseUrl + '/apis/tickets', {
        method: 'POST',
        body: formData
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.type === 'error') {
            errEl.textContent = data.message;
            errEl.style.display = 'block';
        } else {
            successEl.textContent = 'Ticket submitted successfully!';
            successEl.style.display = 'block';
            form.reset();
        }
    });
});
