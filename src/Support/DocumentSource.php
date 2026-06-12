<?php

declare(strict_types=1);

namespace KycAi\Laravel\Support;

use Illuminate\Http\UploadedFile;
use KycAi\Laravel\Exceptions\KycException;
use SplFileInfo;

final class DocumentSource
{
    private function __construct(
        private readonly string $path,
        private readonly ?string $mimeType = null,
        private readonly ?string $originalName = null,
    ) {}

    public static function fromMixed(mixed $source): self
    {
        if ($source instanceof self) {
            return $source;
        }

        if ($source instanceof UploadedFile) {
            $path = $source->getRealPath();

            if ($path === false) {
                throw KycException::invalidDocument('Uploaded file path is not readable.');
            }

            return new self($path, $source->getMimeType() ?: null, $source->getClientOriginalName());
        }

        if ($source instanceof SplFileInfo) {
            return new self($source->getPathname(), null, $source->getFilename());
        }

        if (is_string($source) && is_file($source)) {
            return new self($source, null, basename($source));
        }

        throw KycException::invalidDocument('Document must be an uploaded file, SplFileInfo, or readable file path.');
    }

    public function path(): string
    {
        return $this->path;
    }

    public function mimeType(): ?string
    {
        return $this->mimeType;
    }

    public function originalName(): ?string
    {
        return $this->originalName;
    }

    public function base64(): string
    {
        $contents = file_get_contents($this->path);

        if ($contents === false) {
            throw KycException::invalidDocument('Unable to read document contents.');
        }

        return base64_encode($contents);
    }

    public function delete(): void
    {
        if (is_file($this->path)) {
            @unlink($this->path);
        }
    }
}
