<?php

namespace App\Event;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class PasswordEncoderSubscriber
 * @package App\Event
 *
 * Permet d'encoder le mot de passe de l'utilisateur créé via API Platform
 */
class PasswordEncoderSubscriber implements EventSubscriberInterface
{
    private $userPasswordEncoder;

    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * https://api-platform.com/docs/core/events/
     * On se place avant l'écriture en base de données, et on exécute la fonction encodePassword de ce fichier (de cette classe)
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['encodePassword', EventPriorities::PRE_WRITE]
        ];
    }

    /**
     * https://symfony.com/doc/4.4/reference/events.html#kernel-view
     * C'est dans cette documentation de Symfony qu'on nous indique la classe de l'événement passé en paramètre
     * Cette fonction est appelée avant chaque insertion en base de données via API Platform
     *
     * @param ViewEvent $event
     */
    public function encodePassword(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod(); // POST, GET, PUT...

        //Avant d'encoder le mot de passe, on s'assure qu'on est bien en train de créer un utilisateur
        if ($result instanceof User && $method === 'POST') {
            $user = $result;
            $hash = $this->userPasswordEncoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hash);
        }
    }
}