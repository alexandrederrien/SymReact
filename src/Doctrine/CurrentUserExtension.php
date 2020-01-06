<?php

namespace App\Doctrine;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;
    private $authChecker;

    public function __construct(Security $security, AuthorizationCheckerInterface $authChecker)
    {
        $this->security = $security;
        $this->authChecker = $authChecker;
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass)
    {
        $user = $this->security->getUser();

        //Si l'utilisateur est connecté et n'est pas admin, il doit uniquement voir ses clients et leurs factures
        if ($user instanceof User && !$this->authChecker->isGranted('ROLE_ADMIN') && in_array($resourceClass, [Customer::class, Invoice::class])) {
            $rootAlias = $queryBuilder->getRootAliases()[0];

            if($resourceClass === Customer::class) {
                $queryBuilder
                    ->andWhere($rootAlias.'.user = :user');
            } elseif ($resourceClass === Invoice::class) {
                $queryBuilder
                    ->join($rootAlias.'.customer', 'c')
                    ->andWhere('c.user = :user');
            }

            $queryBuilder->setParameter('user', $user);
        }
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, string $operationName = null)
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, string $operationName = null, array $context = [])
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }
}