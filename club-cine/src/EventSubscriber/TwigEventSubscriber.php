<?php
namespace App\EventSubscriber;

use App\Module\Group\Repository\GroupRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Environment;

class TwigEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Environment $twig,
        private readonly GroupRepository $groupRepository,
        private readonly Security $security
    ) {}

    public function onKernelController(ControllerEvent $event): void
    {
        $user = $this->security->getUser();
        if ($user) {
            // Buscamos los grupos (dueño + invitado) una sola vez para toda la web
            $groups = $this->groupRepository->findGroupsByUser($user);
            $this->twig->addGlobal('groups', $groups);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }
}

?>