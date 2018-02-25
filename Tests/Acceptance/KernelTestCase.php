<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Acceptance;

use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestTrait;
use Dontdrinkandroot\GitkiBundle\Tests\Utils\Application\app\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
abstract class KernelTestCase extends BaseKernelTestCase
{
    use GitRepositoryTestTrait;

    const GIT_REPOSITORY_PATH = '/tmp/gitkitest/repo/';

    public function setUp()
    {
        $this->setUpRepo();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepositoryTargetPath()
    {
        return self::GIT_REPOSITORY_PATH;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getKernelClass()
    {
        return AppKernel::class;
    }
}
