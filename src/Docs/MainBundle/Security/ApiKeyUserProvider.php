<?php
namespace Docs\MainBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

/**
 * Api key user provider
 * @author hbotev
 *
 */
class ApiKeyUserProvider implements UserProviderInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Retrun service name by api key
     * @param string $apiKey
     */
    public function getServiceForApiKey($apiKey)
    {
        $serviceRepo = $this->em->getRepository("\Docs\CommonBundle\Entity\Service");

        return $serviceRepo->findOneBy(['serviceKey' => $apiKey]);
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\User\UserProviderInterface::loadUserByUsername()
     */
    public function loadUserByUsername($username)
    {
        return new User(
            $username,
            null,
            ["ROLE_REST"]
        );
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\User\UserProviderInterface::refreshUser()
     */
    public function refreshUser(UserInterface $user)
    {
        throw new UnsupportedUserException();
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Security\Core\User\UserProviderInterface::supportsClass()
     */
    public function supportsClass($class)
    {
        return 'Symfony\Component\Security\Core\User\User' === $class;
    }
}
