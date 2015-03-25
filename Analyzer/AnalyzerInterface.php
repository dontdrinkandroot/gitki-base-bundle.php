<?php

namespace Dontdrinkandroot\GitkiBundle\Analyzer;

use Dontdrinkandroot\Path\FilePath;

interface AnalyzerInterface
{

    /**
     * @return string[]
     */
    public function getSupportedExtensions();

    /**
     * @param FilePath $path
     *
     * @return AnalyzedFile
     */
    public function analyze(FilePath $path);
}
