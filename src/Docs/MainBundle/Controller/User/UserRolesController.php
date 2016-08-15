<?php
namespace Docs\MainBundle\Controller\User;

use Docs\MainBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Docs\CommonBundle\Entity\UserRoles;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;

/**
 *
 * @RouteResource("user-roles")
 * User roles controller
 * @author hbotev
 *
 */
class UserRolesController extends AbstractController implements ClassResourceInterface
{
    protected $entityClass = "\Docs\CommonBundle\Entity\UserRoles";

    public function cgetAction(Request $request)
    {
        return $this->listAll();
    }

    /**
     * @param integer $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAction($id)
    {
        return $this->findByID($id);
    }

    public function postAction(Request $request)
    {
       // not sure if this should be possible yet
    }

    public function putAction(Request $request, $id)
    {
        return $this->updateEntity($id, $request->request->all());
    }
}
