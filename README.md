# medas-files

Part of the [Medas framework](https://github.com/tarantuli/medas-core).

## Description

Provides content hashing and MIME type detection for `File` value objects (from `medas-core`). Both services use a `WeakMap` to memoise results per `File` instance, so repeated calls for the same object are free.

`ContentHashManager` computes a SHA-1 hash of `File::$content` on first access and caches it in a `WeakMap` keyed on the `File` instance.

`MimetypeManager` detects the MIME type by writing the file content to a temporary file (via `medas-file-system`'s `TemporaryFiles`) and passing the path to `finfo_file()` with `FILEINFO_MIME_TYPE`. The result is cached per `File` instance. `forFilePath()` is also available for direct path-based detection without a `File` object. The `finfo` scanner is re-initialised on unserialise so the service survives serialisation/cache round-trips.

## Usage

### Package developer context

Register the package and inject `ContentHashManager` or `MimetypeManager`:

```php
use Medas\Files\FilesPackage;

FilesPackage::instance();
```

**Getting the SHA-1 hash of a file:**

```php
use Medas\Files\ContentHashManager;
use Medas\Core\{Attributes\Service, File};

#[Service]
readonly class DeduplicationService
{
    public function __construct(
        private ContentHashManager $hashManager,
    ) {}

    public function isDuplicate(File $incoming, File $existing): bool
    {
        // Result is memoised per File instance
        return $this->hashManager->get($incoming) === $this->hashManager->get($existing);
    }

    public function hash(File $file): string
    {
        return $this->hashManager->get($file);
    }
}
```

**Detecting the MIME type of a file:**

```php
use Medas\Files\MimetypeManager;
use Medas\Core\{Attributes\Service, File};

#[Service]
readonly class UploadValidator
{
    private const array ALLOWED_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf',
    ];

    public function __construct(
        private MimetypeManager $mimetypeManager,
    ) {}

    public function validate(File $file): void
    {
        // Detected from file content via finfo, not from the file extension
        $mime = $this->mimetypeManager->get($file);

        if (!in_array($mime, self::ALLOWED_TYPES, true)) {
            throw new \InvalidArgumentException(
                "File type '$mime' is not allowed."
            );
        }
    }
}
```

**Detecting the MIME type from a path on disk:**

```php
// Useful when you have a filesystem path rather than a File object
$mime = $mimetypeManager->forFilePath('/var/www/uploads/document.pdf');
// → 'application/pdf'
```

**Using both together — storing a file with deduplication:**

```php
use Medas\Files\{ContentHashManager, MimetypeManager};
use Medas\Core\{Attributes\Service, File};

#[Service]
readonly class FileStore
{
    public function __construct(
        private ContentHashManager $hashManager,
        private MimetypeManager    $mimetypeManager,
    ) {}

    public function store(File $file): array
    {
        return [
            'hash'      => $this->hashManager->get($file),
            'mime_type' => $this->mimetypeManager->get($file),
            'size'      => strlen($file->content),
        ];
    }
}
```

### Backend user context

Both services are consumed transparently through injection — there are no CLI commands in this package. A few practical notes:

**MIME detection is content-based**, not extension-based. A JPEG renamed to `.txt` will still be detected as `image/jpeg`. This makes it reliable for upload validation.

**Temporary files** — `MimetypeManager` writes content to a temp file for each unique `File` instance (first access only). The temp file is cleaned up automatically by `TemporaryFiles` on PHP shutdown.

**Memoisation scope** — results are cached in a `WeakMap` for the lifetime of the `MimetypeManager` / `ContentHashManager` service instance (i.e., one request). The `File` object must remain in scope for the cache entry to be retained; when the `File` is garbage-collected, the cached result is released automatically.
