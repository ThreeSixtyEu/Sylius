<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sylius\Component\Rbac\Authorization;

use Sylius\Component\Rbac\Model\IdentityInterface;
use Sylius\Component\Rbac\Model\RoleInterface;
use Sylius\Component\Rbac\Provider\CurrentIdentityProviderInterface;
use Sylius\Component\Rbac\Resolver\RolesResolverInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Default authorization checker.
 *
 * @author Paweł Jędrzejewski <pawel@sylius.org>
 */
class AuthorizationChecker implements AuthorizationCheckerInterface
{
    /**
     * @var CurrentIdentityProviderInterface
     */
    protected $currentIdentityProvider;

    /**
     * @var PermissionMapInterface
     */
    protected $permissionMap;

    /**
     * @var RolesResolverInterface
     */
    protected $rolesResolver;

    /**
     * @param CurrentIdentityProviderInterface $currentIdentityProvider
     * @param PermissionMapInterface $permissionMap
     * @param RolesResolverInterface $rolesResolver
     */
    public function __construct(
        CurrentIdentityProviderInterface $currentIdentityProvider,
        PermissionMapInterface $permissionMap,
        RolesResolverInterface $rolesResolver
    )
    {
        $this->currentIdentityProvider = $currentIdentityProvider;
        $this->permissionMap = $permissionMap;
        $this->rolesResolver = $rolesResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($permissionCode)
    {
        $roles = $this->getRoles();

        if (false === $roles) {
            return false;
        }

        foreach ($roles as $role) {
            if ($this->permissionMap->hasPermission($role, $permissionCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function hasRole($roleCode)
    {
        $roles = $this->getRoles();

        if (false === $roles) {
            return false;
        }

        foreach ($roles as $role) {
            if ($role->getCode() === $roleCode) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array|boolean|RoleInterface[]
     */
    protected function getRoles()
    {
        $identity = $this->currentIdentityProvider->getIdentity();

        if (null === $identity) {
            return false;
        }

        if (!$identity instanceof IdentityInterface) {
            throw new \InvalidArgumentException('Current identity must implement "Sylius\Component\Rbac\Model\IdentityInterface".');
        }

        return $this->rolesResolver->getRoles($identity);
    }
}
