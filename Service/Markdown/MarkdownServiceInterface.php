<?php


namespace Dontdrinkandroot\GitkiBundle\Service\Markdown;

use Dontdrinkandroot\GitkiBundle\Model\Document\ParsedMarkdownDocument;
use Dontdrinkandroot\Path\FilePath;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
interface MarkdownServiceInterface
{

    /**
     * @param string   $content
     *
     * @param FilePath $path The path of the document to parse. Used to resolve references.
     *
     * @return ParsedMarkdownDocument
     */
    public function parse($content, FilePath $path);
}
