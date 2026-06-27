# File Uploads — Streaming, Resumable, and Memory-Safe

Example application for the blog post: [File Uploads in WebFiori v3](https://webfiori.com/blog/file-uploads)

## What This Demonstrates

- **Standard uploads** via `FileUploader` (multipart/form-data HTML forms)
- **Streaming uploads** via `StreamingUploader` (raw binary body, constant memory)
- **Resumable uploads** via `ResumableUploader` (chunked with pause/resume)
- Extension filtering, size limits, and stream processors

## Running

```bash
composer install
php -S localhost:8080 -t public
```

## API Endpoints

### Standard Upload (multipart/form-data)

```bash
# Upload a file
curl -X POST http://localhost:8080/apis/upload \
  -F "file=@document.pdf"

# List uploaded files
curl http://localhost:8080/apis/upload
```

### Streaming Upload (raw binary body)

```bash
curl -X POST http://localhost:8080/apis/stream-upload \
  -H "Content-Type: application/octet-stream" \
  -H "X-Filename: report.pdf" \
  --data-binary @report.pdf
```

### Resumable Chunked Upload

```bash
# Send chunk 1
head -c 8192 large-file.zip | curl -X POST http://localhost:8080/apis/chunk-upload \
  -H "Content-Type: application/octet-stream" \
  -H "X-Upload-Id: my-session-id" \
  -H "X-Filename: large-file.zip" \
  -H "X-Is-Last: 0" \
  --data-binary @-

# Check offset (for resume)
curl "http://localhost:8080/apis/chunk-upload?upload-id=my-session-id&filename=large-file.zip"

# Send final chunk
tail -c +8193 large-file.zip | curl -X POST http://localhost:8080/apis/chunk-upload \
  -H "Content-Type: application/octet-stream" \
  -H "X-Upload-Id: my-session-id" \
  -H "X-Filename: large-file.zip" \
  -H "X-Is-Last: 1" \
  --data-binary @-

# Cancel an upload
curl -X DELETE "http://localhost:8080/apis/chunk-upload?upload-id=my-session-id&filename=large-file.zip"
```

## Running Tests

```bash
composer test
```

## Related

- [Blog post](https://webfiori.com/blog/file-uploads)
- [Uploading Files docs](https://webfiori.com/docs/uploading-files)
- [Streaming Uploads docs](https://webfiori.com/docs/streaming-uploads)
- [Resumable Uploads docs](https://webfiori.com/docs/resumable-uploads)
