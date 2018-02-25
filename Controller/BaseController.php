<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class BaseController extends Controller
{

    const ANONYMOUS_ROLE = 'IS_AUTHENTICATED_ANONYMOUSLY';

    /**
     * @var SecurityService
     */
    protected $securityService;

    /**
     * BaseController constructor.
     */
    public function __construct(SecurityService $securityService)
    {
        $this->securityService = $securityService;
    }

    /**
     * Generate an etag based on the timestamp and the current user.
     *
     * @param \DateTime $timeStamp
     *
     * @return string The generated etag.
     */
    protected function generateEtag(\DateTime $timeStamp)
    {
        $user = $this->securityService->findGitUser();
        $userString = '';
        if (null !== $user) {
            $userString = $user->getGitUserName();
        }

        return md5($timeStamp->getTimestamp() . $userString);
    }
}
