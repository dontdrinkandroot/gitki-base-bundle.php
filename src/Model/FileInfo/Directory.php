<?php

namespace Dontdrinkandroot\GitkiBundle\Model\FileInfo;

use Dontdrinkandroot\Path\DirectoryPath;
use JsonSerializable;

class Directory extends AbstractPathAwareFileInfo implements JsonSerializable
{
    public readonly DirectoryPath $relativePath;

    public readonly DirectoryPath $absolutePath;

    public function __construct($basePath, $currentDirectoryPath, $relativeDirectoryPath)
    {
        parent::__construct($basePath . $currentDirectoryPath . $relativeDirectoryPath);
        $this->absolutePath = DirectoryPath::parse($currentDirectoryPath . $relativeDirectoryPath);
        $this->relativePath = DirectoryPath::parse($relativeDirectoryPath);
    }

    /**
     * {@inheritdoc}
     */
    public function getRelativePath(): DirectoryPath
    {
        return $this->relativePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getAbsolutePath(): DirectoryPath
    {
        return $this->absolutePath;
    }

    /**
     * {@inheritdoc}
     * @return array{path: string}
     */
    public function jsonSerialize(): array
    {
        return [
            'path' => $this->getAbsolutePath()->toAbsoluteString()
        ];
    }
}
