<?php

namespace Dontdrinkandroot\GitkiBundle\Analyzer;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\Path\FilePath;

class TextAnalyzer implements AnalyzerInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(FilePath $filePath, ?string $mimeType): bool
    {
        return $mimeType === 'text/plain';
    }

    /**
     * {@inheritdoc}
     */
    public function analyze(FilePath $path, $content): AnalyzedDocument
    {
        $analyzedFile = new AnalyzedDocument(
            path: $path,
            analyzedContent: $content,
            title: $path->getName(),
            content: $content
        );

        return $analyzedFile;
    }
}
