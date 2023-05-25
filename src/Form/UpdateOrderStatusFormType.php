<?php

namespace App\Form;

use App\Service\DataMapping;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateOrderStatusFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dataMapping = new DataMapping();
        $orderStatuses = array_flip($dataMapping->getData('order_statuses'));

        $builder
            ->add('order_status', ChoiceType::class, [
                'choices' => $orderStatuses,
                'placeholder' => array_search($options['attr']['placeholder'], $orderStatuses)
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
