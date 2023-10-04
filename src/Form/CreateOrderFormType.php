<?php

namespace App\Form;

use App\Entity\Order;
use App\Service\DataMapping;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;

class CreateOrderFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $dataMapping = new DataMapping();
        $waysToGet = array_flip($dataMapping->getData('order_ways_to_get'));
        $paymentTypes = array_flip($dataMapping->getData('order_payment_types'));
        $deliveryTypes = array_flip($dataMapping->getData('order_delivery_types'));

        $builder
            ->add('delivery_type', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => $deliveryTypes,
                'data' => array_keys(array_flip($deliveryTypes))[0]
            ])
            ->add('way_to_get', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => $waysToGet,
                'data' => array_keys(array_flip($waysToGet))[1],
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
            ->add('email', TextType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'placeholder' => 'Email(Необязательно)',
                    'class' => 'form-control'
                ],
                'constraints' => [new Email()]
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
                    'class' => 'form-control',
                    'autocomplete' => 'off'
                ],
                'label' => false
            ])
            ->add('payment_type', ChoiceType::class, [
                'expanded' => true,
                'multiple' => false,
                'choices' => $paymentTypes,
                'data' => array_keys(array_flip($paymentTypes))[0]
            ])
            ->add('addressGeocoords', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
