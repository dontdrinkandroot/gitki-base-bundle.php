<?php

namespace Dontdrinkandroot\GitkiBundle\Exception;

use Exception;

class ComitMessageMissingException extends Exception
{
    public function __construct()
    {
        parent::__construct('Commit message is missing');
    }
}
