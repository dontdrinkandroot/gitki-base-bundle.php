<?php


namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class FileController extends BaseController
{

    public function serveAction(Request $request, $path)
    {
        $this->assertWatcher();

        $filePath = FilePath::parse($path);

        $file = $this->getWikiService()->getFile($filePath);

        $response = new Response();
        $lastModified = new \DateTime();
        $lastModified->setTimestamp($file->getMTime());
        $response->setLastModified($lastModified);
        if ($response->isNotModified($request)) {
            return $response;
        }

        $response->headers->set('Content-Type', $file->getMimeType());
        $response->setContent($this->getContents($file));

        return $response;
    }

    public function deleteAction($path)
    {
        $this->assertCommitter();

        $filePath = FilePath::parse($path);
        $user = $this->getGitUser();

        $commitMessage = 'Removing ' . $filePath->toAbsoluteString();
        $this->getWikiService()->deleteFile($user, $filePath, $commitMessage);

        return $this->redirectToRoute(
            'ddr_gitki_directory',
            ['path' => $filePath->getParentPath()->toAbsoluteString()]
        );
    }

    public function holdLockAction($path)
    {
        $this->assertCommitter();

        $filePath = FilePath::parse($path);
        $user = $this->getGitUser();

        $expiry = $this->getWikiService()->holdLock($user, $filePath);

        return new Response($expiry);
    }

    public function historyAction($path)
    {
        $this->assertWatcher();

        $filePath = FilePath::parse($path);

        $history = $this->getWikiService()->getFileHistory($filePath);

        return $this->render(
            'DdrGitkiBundle:File:history.html.twig',
            [
                'path'    => $filePath,
                'history' => $history
            ]
        );
    }

    public function moveAction(Request $request, $path)
    {
        $this->assertCommitter();

        $filePath = FilePath::parse($path);
        $user = $this->getGitUser();

        try {
            $this->getWikiService()->createLock($user, $filePath);
        } catch (FileLockedException $e) {
            throw new ConflictHttpException($e->getMessage());
        }

        $directories = $this->getDirectoryService()->findSubDirectories(new DirectoryPath());
        $directoryChoices = [];
        foreach ($directories as $directory) {
            $directoryChoices[$directory->toAbsoluteString()] = $directory->toAbsoluteString();
        }

        $form = $this->createFormBuilder()
            ->add(
                'directory',
                'choice',
                [
                    'choices'  => $directoryChoices,
                    'required' => true,
                    'data'     => $filePath->getParentPath()->toAbsoluteString()
                ]
            )
            ->add('name', 'text', ['required' => true, 'data' => $filePath->getName()])
            ->add('rename', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $newDirectory = DirectoryPath::parse($form->get('directory')->getData());
                $newName = $form->get('name')->getData();
                $newPath = $newDirectory->appendFile($newName);

                $this->getWikiService()->renameFile(
                    $user,
                    $filePath,
                    $newPath,
                    sprintf('Moving %s to %s', $filePath->toAbsoluteString(), $newPath->toAbsoluteString())
                );

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_directory',
                        ['path' => $newPath->getParentPath()->toAbsoluteString()]
                    )
                );
            }
        }

        return $this->render(
            'DdrGitkiBundle:File:move.html.twig',
            ['form' => $form->createView(), 'path' => $filePath]
        );
    }

    /**
     * @param File $file
     *
     * @return string
     * @throws \RuntimeException
     */
    protected function getContents(File $file)
    {
        $level = error_reporting(0);
        $content = file_get_contents($file->getPathname());
        error_reporting($level);
        if (false === $content) {
            $error = error_get_last();
            throw new \RuntimeException($error['message']);
        }

        return $content;
    }
}
