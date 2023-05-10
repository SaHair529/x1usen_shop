<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateOrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('way_to_get', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Доставка курьером' => 'courier_delivery',
                    'Самовывоз' => 'pickup'
                ],
                'data' => 'courier_delivery',
            ])
            ->add('client_fullname', TextType::class, [
                'attr' => [
                    'placeholder' => 'ФИО',
                    'class' => 'form-control'
                ],
                'label' => false
            ])
            ->add('phone_number', TextType::class, [
                'attr' => [
                    'placeholder' => 'Номер телефона',
                    'class' => 'form-control'
                ],
                'label' => false
            ])
            ->add('city', TextType::class, [
                'attr' => [
                    'placeholder' => 'Город',
                    'class' => 'form-control'
                ],
                'label' => false
            ])
            ->add('address', TextType::class, [
                'attr' => [
                    'placeholder' => 'Адрес',
                    'class' => 'form-control'
                ],
                'label' => false
            ])
            ->add('payment_type', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'Онлайн' => 'online',
                    'Наличными' => 'offline'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
