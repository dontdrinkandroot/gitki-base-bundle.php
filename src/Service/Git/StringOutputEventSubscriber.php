<?php

namespace Dontdrinkandroot\GitkiBundle\Service\Git;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symplify\GitWrapper\Event\GitOutputEvent;

class StringOutputEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            GitOutputEvent::class => 'handleOutput',
        ];
    }

    private string $buffer = '';

    public function handleOutput(GitOutputEvent $gitOutputEvent): void
    {
        $this->buffer .= $gitOutputEvent->getBuffer();
    }

    public function getBuffer(): string
    {
        return $this->buffer;
    }
}
