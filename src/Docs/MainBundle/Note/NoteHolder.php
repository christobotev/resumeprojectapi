<?php
namespace Docs\MainBundle\Note;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerAware;

class NoteHolder extends ContainerAware
{
    public function prepareData(Request $request)
    {
        if ($request->request->has('Note')) {
            $note = $request->get('Note');
            $user = $this->getUserReference($note['user']);

            // replace strings with objects
            $note['user'] = $user;
            $note['created'] = new \DateTime($note['created']);
            return $note;
        }
    }

    /**
     * Get entity reference
     * @return object \Docs\CommonBundle\Entity\User
     */
    protected function getUserReference($userID)
    {
        $entityManager = $this->get('doctrine.orm.entity_manager');
        /* @var $entityManager \Doctrine\ORM\EntityManager */
        $userEntity = $entityManager->getReference(
            "Docs\CommonBundle\Entity\User",
            $userID
            );

        return $userEntity;
    }

    /**
     * Return service from the container
     * @param string $service
     * @return object
     */
    protected function get($service)
    {
        return $this->container->get($service);
    }
}