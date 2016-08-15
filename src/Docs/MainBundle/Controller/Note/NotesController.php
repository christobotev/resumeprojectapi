<?php
namespace Docs\MainBundle\Controller\Note;

use Docs\MainBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Docs\CommonBundle\Entity\Note;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;

/**
 * Notes service
 * @author hbotev
 *
 */
class NotesController extends AbstractController implements ClassResourceInterface
{
    protected $entityClass = "\Docs\CommonBundle\Entity\Note";

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
        if (!$request->get('Note')) {
            return $this->returnError("Bad request", Codes::HTTP_BAD_REQUEST);
        };

        $noteHolder = $this->get('note.holder');
        /* @var $noteHolder \Docs\MainBundle\Note\NoteHolder */

        $noteProcessor = $this->get("note.processor");
        /* @var $noteProcessor \Docs\MainBundle\Note\NoteProcessor */

        $noteEntity = $noteProcessor->processNote($noteHolder->prepareData($request));

        if (!$noteEntity) {
            return $this->returnError("Internal server error", Codes::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->returnResponse(["result" => $noteEntity], 201);
    }

    public function putAction(Request $request, $id)
    {
        // not yet
//         return $this->updateEntity($id, $request->request->all());
    }
}
