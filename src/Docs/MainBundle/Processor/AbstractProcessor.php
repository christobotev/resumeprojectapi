<?php
namespace Docs\MainBundle\Processor;

use Doctrine\ORM\EntityManager;
use Docs\CommonBundle\Entity\Reminder;
use Docs\CommonBundle\Entity\User;
use Docs\CommonBundle\Entity\Note;
use Docs\CommonBundle\Entity\Appointment;
use Docs\MainBundle\Rating\RatingHolder;
use Docs\CommonBundle\Entity\Rating;

abstract class AbstractProcessor
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Persist Note entity
     * @param string $noteStr
     * @return \Docs\CommonBundle\Entity\Note
     */
    protected function processNote($noteStr, User $user)
    {
        $note = new Note();
        $note->setContent($noteStr);
        $note->setUser($user);
        $note->setCreated(new \DateTime());
        $this->entityManager->persist($note);
        return $note;
    }

    /**
     * Close open reminders
     * @param int $userID
     */
    protected function closeOpenReminders(User $user, User $withUser)
    {
        $reminderRepo = $this->entityManager->getRepository("Docs\CommonBundle\Entity\Reminder");
        /* @var $reminderRepo \Docs\CommonBundle\Repository\ReminderRepository */

        $reminders = $reminderRepo->getOpenReminder($user, $withUser);

        foreach ($reminders as $reminder) {
            $reminder->setStatus(Reminder::STATUS_CLOSED);
            $this->entityManager->persist($reminder);
        }
    }

    protected function processAppointment($appData, $notePersisted, $status)
    {
        $appointment = new Appointment();
        $appointment->setCreated(new \DateTime());
        $appointment->setScheduled($appData['datetime']);
        $appointment->setNote($notePersisted);
        $appointment->setUser($appData['user']);
        $appointment->setWithUser($appData['withUser']);
        $appointment->setStatus($status);

        $this->entityManager->persist($appointment);
        return $appointment;
    }

    /**
     * Process Reminder with the given status
     * @param array $appData
     * @param Note $notePersisted
     * @param integer $status
     */
    protected function processReminder($redminerData, $notePersisted, $status)
    {
        $reminder = new Reminder();
        $reminder->setUser($redminerData['withUser']);
        $reminder->setNote($notePersisted);
        $reminder->setScheduled($redminerData['scheduled']);
        $reminder->setStatus($status);
        $reminder->setCreated(new \DateTime());
        $reminder->setCreatedBy($redminerData['createdBy']);

        $this->entityManager->persist($reminder);
        return $reminder;
    }

    protected function processRating(
                RatingHolder $ratingHolder,
                Note $notePersisted,
                User $user,
                User $createdBy
    ) {
        $rating = new Rating();
        $rating->setRating($ratingHolder->getRating());
        $rating->setNote($notePersisted);
        $rating->setUser($user);
        $rating->setCreatedBy($createdBy);
        $rating->setCreated(new \DateTime());

        $this->entityManager->persist($rating);
    }
}