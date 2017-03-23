<?php

namespace Dontdrinkandroot\GitkiBundle\Event;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class FileDeletedEvent extends AbstractFileEvent
{
    const NAME = 'ddr.gitki.file.deleted';

    public function __construct(GitUserInterface $user, $commitMessage, $time, FilePath $file)
    {
        parent::__construct($user, $commitMessage, $time, $file);
    }
}
