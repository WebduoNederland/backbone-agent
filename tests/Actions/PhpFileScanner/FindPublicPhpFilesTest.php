<?php

namespace WebduoNederland\BackboneAgent\Tests\Actions\PhpFileScanner;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use WebduoNederland\BackboneAgent\Actions\PhpFileScanner\FindPublicPhpFiles;
use WebduoNederland\BackboneAgent\Tests\TestCase;

class FindPublicPhpFilesTest extends TestCase
{
    protected string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().'/backbone-agent-'.uniqid();

        File::ensureDirectoryExists($this->directory.'/public');

        $this->app?->usePublicPath($this->directory.'/public');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->directory);

        parent::tearDown();
    }

    #[Test]
    public function it_returns_nothing_when_only_allowed_files_exist(): void
    {
        $this->createFile('index.php');
        $this->createFile('robots.txt');
        $this->createFile('build/app.js');

        $this->assertSame([], app(FindPublicPhpFiles::class)->find());
    }

    #[Test]
    public function it_finds_php_files_in_the_public_folder_and_all_sub_folders(): void
    {
        $this->createFile('index.php');
        $this->createFile('shell.php');
        $this->createFile('.hidden.php');
        $this->createFile('uploads/index.php');
        $this->createFile('uploads/2026/10/evil.PHTML');
        $this->createFile('uploads/2026/10/image.php.png');
        $this->createFile('.well-known/backdoor.phar');

        $this->assertSame([
            '.hidden.php',
            '.well-known/backdoor.phar',
            'shell.php',
            'uploads/2026/10/evil.PHTML',
            'uploads/index.php',
        ], app(FindPublicPhpFiles::class)->find());
    }

    #[Test]
    public function it_follows_symlinked_folders(): void
    {
        File::ensureDirectoryExists($this->directory.'/storage/app/public');
        File::put($this->directory.'/storage/app/public/avatar.php', '<?php');

        symlink($this->directory.'/storage/app/public', $this->directory.'/public/storage');

        $this->assertSame(['storage/avatar.php'], app(FindPublicPhpFiles::class)->find());
    }

    #[Test]
    public function it_uses_the_configured_allowed_files_and_extensions(): void
    {
        config()->set('backbone-agent.php_file_scanner.allowed_files', ['index.php', 'legacy/api.php']);
        config()->set('backbone-agent.php_file_scanner.extensions', ['php']);

        $this->createFile('index.php');
        $this->createFile('legacy/api.php');
        $this->createFile('legacy/other.php');
        $this->createFile('template.phtml');

        $this->assertSame(['legacy/other.php'], app(FindPublicPhpFiles::class)->find());
    }

    #[Test]
    public function it_returns_nothing_when_the_public_folder_does_not_exist(): void
    {
        $this->app?->usePublicPath($this->directory.'/missing');

        $this->assertSame([], app(FindPublicPhpFiles::class)->find());
    }

    protected function createFile(string $path): void
    {
        File::ensureDirectoryExists(dirname($this->directory.'/public/'.$path));

        File::put($this->directory.'/public/'.$path, '<?php');
    }
}
