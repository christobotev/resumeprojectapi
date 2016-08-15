<?php
namespace Docs\MainBundle\Controller\Rating;

use Docs\MainBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Docs\CommonBundle\Entity\Rating;
use FOS\RestBundle\Routing\ClassResourceInterface;

/**
 * Ratings service
 * @author hbotev
 *
 */
class RatingsController extends AbstractController implements ClassResourceInterface
{
    protected $entityClass = "\Docs\CommonBundle\Entity\Rating";

    public function cgetAction(Request $request)
    {
        return $this->listAll();
    }

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
