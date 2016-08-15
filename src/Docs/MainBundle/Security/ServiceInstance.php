<?php
namespace Docs\MainBundle\Security;

use Docs\CommonBundle\Entity\Service;

/**
 * Container for the service data from api authentication
 * @author hbotev
 *
 */
class ServiceInstance
{
    /**
     * @var \Docs\CommonBundle\Entity\Service
     */
    protected $serviceEntity;

    /**
     * Init object
     * @param Service $service
     */
    public function __construct(Service $service)
    {
        $this->serviceEntity = $service;
    }

    /**
     * Return the service entity
     * @return \Docs\CommonBundle\Entity\Service
     */
    public function getServiceEntity()
    {
        return $this->serviceEntity;
    }

    /**
     * Return the name of the service
     * @return string
     */
    public function __toString()
    {
        return $this->getServiceEntity()->getName();
    }
}
