<?php

namespace App\Event;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Exception;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class InvoiceSubscriber implements EventSubscriberInterface
{
    private $security;
    private $invoiceRepository;

    public function __construct(Security $security, InvoiceRepository $invoiceRepository)
    {
        $this->security = $security;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * https://api-platform.com/docs/core/events/
     * On se place avant la validation des données en base de données, et on exécute la fonction setChronoForInvoice de ce fichier (de cette classe)
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [
                'setPreValidateInfosForInvoice', EventPriorities::PRE_VALIDATE
            ]
        ];
    }

    /**
     * https://symfony.com/doc/4.4/reference/events.html#kernel-view
     * C'est dans cette documentation de Symfony qu'on nous indique la classe de l'événement passé en paramètre
     * Cette fonction est appelée avant chaque validation des données via API Platform
     *
     * @param ViewEvent $event
     * @throws Exception
     */
    public function setPreValidateInfosForInvoice(ViewEvent $event)
    {
        $result = $event->getControllerResult();
        $method = $event->getRequest()->getMethod(); // POST, GET, PUT...

        //Avant d'encoder le mot de passe, on s'assure qu'on est bien en train de créer un client
        if ($result instanceof Invoice && $method === 'POST') {
            $invoice = $result;
            $nextChrono = $this->invoiceRepository->getNextChronoByUser($this->security->getUser());
            $invoice->setChrono($nextChrono);

            if (empty($invoice->getSentAt())) {
                $invoice->setSentAt(new \DateTime());
            }
        }
    }
}