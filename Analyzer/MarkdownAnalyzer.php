<?php

namespace Dontdrinkandroot\GitkiBundle\Analyzer;

use Dontdrinkandroot\GitkiBundle\Analyzer\AnalyzedFile;
use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\GitkiBundle\Service\Markdown\MarkdownServiceInterface;
use Dontdrinkandroot\Path\FilePath;

class MarkdownAnalyzer implements AnalyzerInterface
{

    /**
     * @var MarkdownServiceInterface
     */
    private $markdownService;

    public function __construct(MarkdownServiceInterface $markdownService)
    {
        $this->markdownService = $markdownService;
    }

    /**
     * {@inheritdoc}
     */
    public function getSupportedExtensions()
    {
        return ['md'];
    }

    /**
     * {@inheritdoc}
     */
    public function analyze(FilePath $path, $content)
    {
        $markdownDocument = $this->markdownService->parse($path, $content);
        $analyzedDocument = new AnalyzedDocument($path);
        $analyzedDocument->setTitle($markdownDocument->getTitle());
        $analyzedDocument->setContent($content);
        $analyzedDocument->setLinkedPaths($markdownDocument->getLinkedPaths());
        $analyzedDocument->setAnalyzedContent($content);

        return $analyzedDocument;
    }
}
