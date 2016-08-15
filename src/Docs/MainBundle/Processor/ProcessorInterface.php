<?php
namespace Docs\MainBundle\Processor;

use Symfony\Component\Form\FormInterface;
use Docs\CommonBundle\Entity\User;

/**
 * Interface that must be implemented by the processors for
 * appointments/reminders
 * @author h.botev
 *
 */
interface ProcessorInterface
{
    /**
     * Handle the submission of a result form
     * @param FormInterface $resultForm
     * @param User $user
     * @param User $withUser
     */
    public function process(FormInterface $form, User $user, User $withUser);
}
