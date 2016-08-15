<?php
namespace Docs\MainBundle\Form\Validators;

use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * For form fields that are not
 * required in the entity
 *
 * @author h.botev
 */
class DateNotEmptyValidator extends ContainerAware
{
    private $msg = 'Date field can\'t be empty';

    public function validate($data, ExecutionContextInterface $context)
    {
        if (!$data instanceof \DateTime) {
            $context->buildViolation($this->msg)
                    ->setParameter('{{ value }}', 'date')
                    ->addViolation();
        }
    }
}
