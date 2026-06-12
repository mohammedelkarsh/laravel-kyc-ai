<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use Illuminate\Http\UploadedFile;
use KycAi\Laravel\Exceptions\KycException;
use KycAi\Laravel\Support\DocumentSource;
use SplFileInfo;

final class DocumentSourceTest extends TestCase
{
    public function test_from_string_path(): void
    {
        $path = $this->tempFile('doc.jpg', 'binary-data');
        $source = DocumentSource::fromMixed($path);

        $this->assertSame($path, $source->path());
        $this->assertStringEndsWith('doc.jpg', $source->originalName() ?? '');
        $this->assertSame(base64_encode('binary-data'), $source->base64());
    }

    public function test_from_spl_file_info(): void
    {
        $path = $this->tempFile('spl.jpg');
        $source = DocumentSource::fromMixed(new SplFileInfo($path));

        $this->assertSame($path, $source->path());
        $this->assertStringEndsWith('spl.jpg', $source->originalName() ?? '');
    }

    public function test_from_uploaded_file(): void
    {
        $file = UploadedFile::fake()->image('upload.jpg');
        $source = DocumentSource::fromMixed($file);

        $this->assertNotEmpty($source->path());
        $this->assertSame('upload.jpg', $source->originalName());
    }

    public function test_invalid_source_throws(): void
    {
        $this->expectException(KycException::class);

        DocumentSource::fromMixed(['not' => 'a file']);
    }

    public function test_delete_removes_file(): void
    {
        $path = $this->tempFile('to-delete.jpg');
        $source = DocumentSource::fromMixed($path);

        $source->delete();

        $this->assertFileDoesNotExist($path);
    }
}
