<?php
namespace Docs\MainBundle\Controller\Appointment;

use Docs\MainBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Docs\CommonBundle\Entity\Appointment;
use FOS\RestBundle\Controller\Annotations\QueryParam;

/**
 * Appointments service
 * @author hbotev
 *
 */
class AppointmentsController extends AbstractController
{
    protected $entityClass = "\Docs\CommonBundle\Entity\Appointment";

    public function getAppointmentsAction(Request $request)
    {
        return $this->listAll();
    }

    /**
     * @QueryParam(name="id", requirements="\d+", default="1", description="User id")
     * @param integer $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAppointmentAction($id)
    {
        return $this->findByID($id);
    }

    public function postAppointmentsAction(Request $request)
    {
        // not sure if this should be possible yet
    }

    public function putAppointmentsAction(Request $request, $id)
    {
        return $this->updateEntity($id, $request->request->all());
    }
}
