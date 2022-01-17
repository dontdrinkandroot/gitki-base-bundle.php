<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Git;

use Dontdrinkandroot\GitkiBundle\Model\CommitMetadata;
use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Repository\LogParser;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemServiceInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\Path;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symplify\GitWrapper\GitWorkingCopy;
use Symplify\GitWrapper\GitWrapper;

class GitService implements GitServiceInterface
{
    public function __construct(private FileSystemServiceInterface $fileSystemService)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepositoryPath(): DirectoryPath
    {
        return $this->fileSystemService->getBasePath();
    }

    /**
     * {@inheritdoc}
     */
    public function getWorkingCopyHistory(?int $maxCount = null): array
    {
        return $this->getHistory(null, $maxCount);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileHistory(FilePath $path, ?int $maxCount = null): array
    {
        return $this->getHistory($path, $maxCount);
    }

    /**
     * @return list<CommitMetadata>
     */
    public function getHistory(FilePath $path = null, ?int $maxCount = null): array
    {
        $options = ['pretty=format:' . LogParser::getFormatString() => true];
        if (null !== $maxCount) {
            $options['max-count'] = (string)$maxCount;
        }
        if (null !== $path) {
            $options['p'] = $path->toRelativeFileSystemString();
        }

        $workingCopy = $this->getWorkingCopy();

        $outputListener = new StringOutputEventSubscriber();
        $workingCopy->getWrapper()->addOutputEventSubscriber($outputListener);
        $workingCopy->log($options);

        return $this->parseLog($outputListener->getBuffer());
    }

    /**
     * {@inheritdoc}
     */
    public function removeAndCommit(GitUserInterface $author, $paths, $commitMessage): void
    {
        $this->remove($this->toFilePathArray($paths));
        $this->commit($author, $commitMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function moveAndCommit(
        GitUserInterface $author,
        FilePath $oldPath,
        FilePath $newPath,
        string $commitMessage
    ): void {
        $workingCopy = $this->getWorkingCopy();
        $workingCopy->mv($oldPath->toRelativeFileSystemString(), $newPath->toRelativeFileSystemString());
        $this->commit($author, $commitMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(Path $path): bool
    {
        return $this->fileSystemService->exists($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsolutePath(Path $path): Path
    {
        return $path->prepend($this->getRepositoryPath());
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory(DirectoryPath $path): void
    {
        $this->fileSystemService->createDirectory($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getContent(FilePath $path): string
    {
        return file_get_contents($this->getAbsolutePathString($path));
    }

    /**
     * {@inheritdoc}
     */
    public function removeDirectory(DirectoryPath $path): void
    {
        $this->fileSystemService->removeDirectory($path);
    }

    /**
     * {@inheritdoc}
     */
    public function putAndCommitFile(
        GitUserInterface $author,
        FilePath $path,
        string $content,
        string $commitMessage
    ): void {
        $this->fileSystemService->putContent($path, $content);
        $this->add([$path]);
        $this->commit($author, $commitMessage);
    }

    /**
     * {@inheritdoc}
     */
    public function addAndCommitUploadedFile(
        GitUserInterface $author,
        FilePath $path,
        UploadedFile $uploadedFile,
        string $commitMessage
    ): void {
        $uploadedFile->move(
            $this->fileSystemService->getAbsolutePath($path->getParentPath())->toAbsoluteFileSystemString(),
            $path->getName()
        );

        $this->addAndCommitFile($author, $commitMessage, $path);
    }

    protected function getWorkingCopy(): GitWorkingCopy
    {
        $git = new GitWrapper('git');
        $workingCopy = $git->workingCopy($this->fileSystemService->getBasePath()->toAbsoluteFileSystemString());

        return $workingCopy;
    }

    /**
     * @param Path $relativePath
     *
     * @return string
     */
    protected function getAbsolutePathString(Path $relativePath)
    {
        return $this->getAbsolutePath($relativePath)->toAbsoluteFileSystemString();
    }

    /**
     * @param FilePath[]|FilePath $paths
     *
     * @return FilePath[]
     */
    protected function toFilePathArray($paths)
    {
        if (!is_array($paths)) {
            return [$paths];
        }

        return $paths;
    }

    /**
     * @param GitUserInterface $user
     *
     * @return string
     */
    protected function getAuthorString(GitUserInterface $user)
    {
        return sprintf('"%s <%s>"', $user->getGitUserName(), $user->getGitUserEmail());
    }

    /**
     * @param string $log
     *
     * @return CommitMetadata[]
     */
    protected function parseLog($log)
    {
        preg_match_all(LogParser::getMatchString(), $log, $matches);
        $metaData = [];
        $numEntries = count($matches[1]);
        for ($i = 0; $i < $numEntries; $i++) {
            $hash = $matches[1][$i];
            $name = $matches[2][$i];
            $eMail = $matches[3][$i];
            $timeStamp = (int)$matches[4][$i];
            $message = $matches[5][$i];
            $metaData[] = new CommitMetadata($hash, $name, $eMail, $timeStamp, $message);
        }

        return $metaData;
    }

    /**
     * @param GitUserInterface $author
     * @param string           $commitMessage
     */
    protected function commit(GitUserInterface $author, $commitMessage): void
    {
        $this->getWorkingCopy()->commit(
            [
                'm' => $commitMessage,
                'author' => $this->getAuthorString($author)
            ]
        );
    }

    /**
     * @param FilePath[] $paths
     */
    protected function add(array $paths): void
    {
        $workingCopy = $this->getWorkingCopy();
        foreach ($paths as $path) {
            $workingCopy->add($this->escapePath($path));
        }
    }

    /**
     * @param FilePath[] $paths
     */
    protected function remove(array $paths): void
    {
        $workingCopy = $this->getWorkingCopy();
        foreach ($paths as $path) {
            $workingCopy->rm($this->escapePath($path));
        }
    }

    /**
     * @param GitUserInterface $author
     * @param string           $commitMessage
     * @param FilePath         $path
     */
    protected function addAndCommitFile(GitUserInterface $author, $commitMessage, FilePath $path): void
    {
        $this->add([$path]);
        $this->commit($author, $commitMessage);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    protected function escapePath(Path $path)
    {
        return str_replace(" ", "\\ ", $path->toRelativeFileSystemString());
    }
}
