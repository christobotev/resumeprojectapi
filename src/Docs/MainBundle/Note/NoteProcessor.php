<?php
namespace Docs\MainBundle\Note;

use Docs\CommonBundle\Entity\Note;
use Docs\MainBundle\Persistence\Persister;

/**
 * Class that helps process Notes
 * @author hbotev
 *
 */
class NoteProcessor
{
    /**
     * @var Persister
     */
    protected $persister;

    public function __construct(Persister $persister)
    {
        $this->persister = $persister;
    }

    /**
     * @param Request $request
     * @return Form
     */
    public function processNote(array $data)
    {
        $note = new Note();
        $note->setContent($data['content']);
        $note->setUser($data['user']);
        $note->setCreated($data['created']);

        $this->persister->beginTransaction();
        $this->persister->persist($note);
        $this->persister->finishTransaction();

        return $note;
    }
}