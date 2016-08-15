<?php
namespace Docs\MainBundle\Form\Validators;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Validator that checks
 * if the date/datetime field is in the past
 * @author h.botev
 */
class PastDateValidator
{
    private $msg = 'Date can\'t be in the past';

    public function validate($date, ExecutionContextInterface $context)
    {
        $dateNow = (new \DateTime());
        if ($dateNow > $date) {
            $context->buildViolation($this->msg)
                    ->addViolation();
        }
    }
}
