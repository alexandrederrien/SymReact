<?php

namespace App\Controller;

use App\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;

class InvoiceIncrementationController
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function __invoke(Invoice $data) //La variable doit s'appeler data
    {
        $invoice = $data;

        $invoice->setChrono($invoice->getChrono() + 1);

        $this->manager->flush();

        return $invoice;
    }
}