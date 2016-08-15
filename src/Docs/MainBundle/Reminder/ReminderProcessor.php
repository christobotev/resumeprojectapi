<?php
namespace Docs\MainBundle\Reminder;

use Symfony\Component\HttpFoundation\Request;
use Docs\CommonBundle\Entity\Note;
use Docs\MainBundle\Processor\AbstractProcessor;
use Docs\CommonBundle\Entity\Reminder;
use Docs\CommonBundle\Entity\User;

/**
 * Class that helps process Reminder
 * @author hbotev
 *
 */
class ReminderProcessor extends AbstractProcessor
{
    /**
     * Process reminder
     * Save note and then reminder
     * @param Request $request
     */
    public function process(Request $request)
    {
        $reminder = $request->get('Reminder');
        $note = $reminder['note'];
        $createdBy = $this->getUserReference($reminder['createdBy']);
        $withUserRef = $this->getUserReference($reminder['user']);

        try {
            $this->entityManager->beginTransaction();
            // persist the note
            $notePersisted = $this->processNote($note, $createdBy);

            // prepare reminder data
            $redminerData = $this->formatData($withUserRef, $reminder['scheduled'], $createdBy);

            // persist the reminder
            $reminder = $this->processReminder($redminerData, $notePersisted, Reminder::STATUS_OPEN);

            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();

            return $reminder;
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw new \Exception(
                'Reminder creation failed '. $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * prepare reminder data
     * @param User $withUserRef
     * @param string $scheduled
     * @param User $createdBy
     * @return array
     */
    protected function formatData(User $withUserRef, $scheduled, User $createdBy)
    {
        return [
            'withUser' => $withUserRef,
            'scheduled' => new \DateTime($scheduled),
            'createdBy' => $createdBy,
        ];
    }

    /**
     * Get user ref
     * @return object \Docs\CommonBundle\Entity\User
     */
    protected function getUserReference($userID)
    {
        $userEntity = $this->entityManager->getReference(
            "Docs\CommonBundle\Entity\User",
            $userID
        );

        return $userEntity;
    }
}