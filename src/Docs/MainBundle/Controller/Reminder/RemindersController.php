<?php
namespace Docs\MainBundle\Controller\Reminder;

use Docs\MainBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Docs\CommonBundle\Entity\Reminder;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FOS\RestBundle\Util\Codes;

/**
 * Reminders service
 * @author hbotev
 *
 */
class RemindersController extends AbstractController implements ClassResourceInterface
{
    protected $entityClass = "\Docs\CommonBundle\Entity\Reminder";

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
        if (!$request->get('Reminder')) {
            return $this->returnError("Bad request", Codes::HTTP_BAD_REQUEST);
        };

        $reminderProcessor = $this->get("reminder.processor");
        /* @var $reminderProcessor \Docs\MainBundle\Reminder\ReminderProcessor */

        $reminderEntity = $reminderProcessor->process($request);

        if (!$reminderEntity) {
            return $this->returnError("Internal server error", Codes::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->returnResponse(["result" => $reminderEntity], 201);
    }

    public function putAction(Request $request, $id)
    {
        return $this->updateEntity($id, $request->request->all());
    }
}
