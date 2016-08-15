<?php
namespace Docs\MainBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints\Callback;
use Docs\MainBundle\Form\Validators\PastDateValidator;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Docs\CommonBundle\Entity\User;

/**
 * Reminder type form
 * @author hbotev
 */
class ReminderForm extends AbstractType
{

    protected $user;

    public function __construct(User $mdEntity)
    {
        $this->user = $mdEntity;
    }

    /**
     * Builds the Reminder form
     * @param  \Symfony\Component\Form\FormBuilder $builder
     * @param  array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $pastDate = new PastDateValidator();
        $builder
            ->add('datetime', DateType::class, [
                'widget' => 'single_text',
                'html5' => false,
                'constraints' => [
                    new Callback(
                        ['callback' => [$pastDate, 'validate']]
                    )
                ]
            ])
            ->add('md', 'choice', [
                'required' => true,
                'choices' => [$this->user->getUserID() => $this->user->getFirstName() . ' ' . $this->user->getLastName()],
                'attr' => [
                    'value' => $this->user->getFirstName() . ' ' . $this->user->getLastName(),
                    'readonly' => 'readonly'
                ],
                'label' => 'M.D.'
            ])
            ->add('note', TextareaType::class, ['required' => true, 'label' => 'Note'])
            ->add('save', 'submit', ['label' => 'Save'])
            ->getForm();
    }

    /**
     * (non-PHPdoc)
     * @see \Symfony\Component\Form\FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'reminder';
    }
}
