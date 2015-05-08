<?php


namespace Dontdrinkandroot\GitkiBundle\Model\FileInfo;

use Dontdrinkandroot\Path\FilePath;

class File extends AbstractPathAwareFileInfo implements \JsonSerializable
{

    /**
     * @var FilePath
     */
    protected $relativePath;

    /**
     * @var FilePath
     */
    protected $absolutePath;

    /**
     * @var string
     */
    protected $title;

    public function __construct($basePath, $currentDirectoryPath, $relativeFilePath)
    {
        parent::__construct($basePath . $currentDirectoryPath . $relativeFilePath);
        $this->absolutePath = FilePath::parse($currentDirectoryPath . $relativeFilePath);
        $this->relativePath = FilePath::parse($relativeFilePath);
    }

    /**
     * @return FilePath
     */
    public function getRelativePath()
    {
        return $this->relativePath;
    }

    /**
     * @return FilePath
     */
    public function getAbsolutePath()
    {
        return $this->absolutePath;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * {@inheritdoc}
     */
    function jsonSerialize()
    {
        $absolutePath = $this->getAbsolutePath();
        $data = [
            'path'      => $absolutePath->toAbsoluteString(),
            'name'      => $absolutePath->getFileName(),
            'extension' => $absolutePath->getExtension(),
            'title'     => $this->getTitle()
        ];

        return $data;
    }
}
