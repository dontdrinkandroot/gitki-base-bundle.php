<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Unit\Service;

use Dontdrinkandroot\GitkiBundle\Exception\DirectoryNotEmptyException;
use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemService;
use Dontdrinkandroot\GitkiBundle\Service\Git\GitService;
use Dontdrinkandroot\GitkiBundle\Service\Lock\LockService;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestCase;
use Dontdrinkandroot\GitkiBundle\Tests\TestUser;
use Dontdrinkandroot\GitkiBundle\Tests\Utils\User;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Exception;

class WikiServiceTest extends GitRepositoryTestCase
{
    private FileSystemService $fileSystemService;

    private GitService $gitService;

    private LockService $lockService;

    private WikiService $wikiService;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->fileSystemService = new FileSystemService(GitRepositoryTestCase::GIT_REPOSITORY_PATH);
        $this->gitService = new GitService($this->fileSystemService);
        $this->lockService = new LockService($this->fileSystemService);
        $this->wikiService = new WikiService($this->gitService, $this->lockService);
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testFindAllFiles(): void
    {
        /** @var FilePath[] $files */
        $files = $this->wikiService->findAllFiles();
        $this->assertCount(6, $files);

        $expectedFilePaths = [
            '/examples/a filename with spaces.md',
            '/index.md',
            '/examples/textfile.txt',
            '/examples/link-example.md',
            '/examples/toc-example.md',
            '/examples/table-example.md'
        ];
        sort($expectedFilePaths);
        $foundFilePaths = [];
        foreach ($files as $file) {
            $foundFilePaths[] = $file->toAbsoluteFileSystemString();
        }
        sort($foundFilePaths);

        $this->assertEquals($expectedFilePaths, $foundFilePaths);
    }

    public function testFindAllFilesWithPath(): void
    {
        /** @var FilePath[] $files */
        $files = $this->wikiService->findAllFiles(DirectoryPath::parse('/examples/'));
        $this->assertCount(5, $files);

        $expectedFilePaths = [
            '/examples/a filename with spaces.md',
            '/examples/link-example.md',
            '/examples/toc-example.md',
            '/examples/table-example.md',
            '/examples/textfile.txt'
        ];
        sort($expectedFilePaths);
        $foundFilePaths = [];
        foreach ($files as $file) {
            $foundFilePaths[] = $file->toAbsoluteFileSystemString();
        }
        sort($foundFilePaths);

        $this->assertEquals($expectedFilePaths, $foundFilePaths);
    }

    public function testRemoveNonEmptyDirectory(): void
    {
        $this->expectException(DirectoryNotEmptyException::class);
        $this->wikiService->removeDirectory(DirectoryPath::parse('/examples/'));
    }

    public function testRemoveDirectory(): void
    {
        $testDirPath = DirectoryPath::parse('/testDir/');
        $this->wikiService->createFolder($testDirPath);
        $this->assertFileExists(
            $testDirPath->prepend($this->fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );
        $this->wikiService->removeDirectory($testDirPath);
        $this->assertFileDoesNotExist(
            $testDirPath->prepend($this->fileSystemService->getBasePath())->toAbsoluteFileSystemString()
        );
    }

    public function testRemoveDirectoryRecursively(): void
    {
        foreach ($this->getExampleFiles() as $exampleFile) {
            $this->assertFileExists(
                $exampleFile->prepend($this->fileSystemService->getBasePath())->toAbsoluteFileSystemString()
            );
        }

        $testUser = new User('testUser', 'TestUser', 'test@example.com');
        $this->wikiService->removeDirectoryRecursively(
            $testUser,
            DirectoryPath::parse('/examples/'),
            'Removing examples'
        );

        foreach ($this->getExampleFiles() as $exampleFile) {
            $this->assertFileDoesNotExist(
                $exampleFile->prepend($this->fileSystemService->getBasePath())->toAbsoluteFileSystemString()
            );
        }
    }

    public function testRemoveDirectoryRecursivelyWithLock(): void
    {
        $this->expectException(FileLockedException::class);

        $testUser = new User('testUser', 'TestUser', 'test@example.com');
        $this->wikiService->createLock($testUser, $this->getExampleFiles()[0]);
        $otherUser = new User('otherUser', 'OtherUser', 'other@example.com');

        $this->wikiService->removeDirectoryRecursively(
            $otherUser,
            DirectoryPath::parse('/examples/'),
            'Removing examples'
        );

        foreach ($this->getExampleFiles() as $exampleFile) {
            $this->assertFileExists(
                $exampleFile->prepend($this->fileSystemService->getBasePath())->toAbsoluteFileSystemString()
            );
        }
    }

    /**
     * @return FilePath[]
     * @throws Exception
     */
    protected function getExampleFiles()
    {
        return [
            FilePath::parse('/examples/link-example.md'),
            FilePath::parse('/examples/toc-example.md'),
            FilePath::parse('/examples/table-example.md'),
        ];
    }
}
