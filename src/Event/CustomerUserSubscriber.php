<?php

namespace App\Event;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Customer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

/**
 * Class PasswordEncoderSubscriber
 * @package App\Event
 *
 * Permet de lier l'utilisateur courant (qu'on connaît grâce au token) au client créé via API Platform
 */
class CustomerUserSubscriber implements EventSubscriberInterface
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * https://api-platform.com/docs/core/events/
     * On se place avant la validation des données en base de données, et on exécute la fonction setUserForCustomer de ce fichier (de cette classe)
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setUserForCustomer', EventPriorities::PRE_VALIDATE]
        ];
    }

    /**
     * https://symfony.com/doc/4.4/reference/events.html#kernel-view
     * C'est dans cette documentation de Symfony qu'on nous indique la classe de l'événement passé en paramètre
     * Cette fonction est appelée avant chaque validation des données via API Platform
     *
     * @param ViewEvent $event
     */
    public function setUserForCustomer(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod(); // POST, GET, PUT...

        //Avant d'encoder le mot de passe, on s'assure qu'on est bien en train de créer un client
        if ($result instanceof Customer && $method === 'POST') {
            $customer = $result;

            //On récupère l'utilisateur via le token
            $user = $this->security->getUser();

            $customer->setUser($user);
        }
    }
}