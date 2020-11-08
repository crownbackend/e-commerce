<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'attr' => [
                    'class' => "size-111 bor8 stext-102 cl2 p-lr-20",
                    "placeholder" => ""
                ],
                'label' => "register.email",
                "label_attr" => [
                    'class' => "stext-102 cl3"
                ],
                "required" => true
            ])
            ->add('password', PasswordType::class, [
                'attr' => [
                    'class' => "size-111 bor8 stext-102 cl2 p-lr-20",
                    "placeholder" => ""
                ],
                'label' => "register.password",
                "label_attr" => [
                    'class' => "stext-102 cl3"
                ],
                "required" => true
            ])
            ->add('address', TextType::class, [
                'attr' => [
                    'class' => "size-111 bor8 stext-102 cl2 p-lr-20",
                    "placeholder" => ""
                ],
                'label' => "register.address",
                "label_attr" => [
                    'class' => "stext-102 cl3"
                ],
                "required" => true
            ])
            ->add('city', TextType::class, [
                'attr' => [
                    'class' => "size-111 bor8 stext-102 cl2 p-lr-20",
                    "placeholder" => ""
                ],
                'label' => "register.city",
                "label_attr" => [
                    'class' => "stext-102 cl3"
                ],
                "required" => true
            ])
            ->add('telephone', TelType::class, [
                'attr' => [
                    'class' => "size-111 bor8 stext-102 cl2 p-lr-20",
                    "placeholder" => ""
                ],
                'label' => "register.phone",
                "label_attr" => [
                    'class' => "stext-102 cl3"
                ],
                "required" => true
            ])
            ->add('firstName', TextType::class, [
                'attr' => [
                    'class' => "size-111 bor8 stext-102 cl2 p-lr-20",
                    "placeholder" => ""
                ],
                'label' => "register.firstName",
                "label_attr" => [
                    'class' => "stext-102 cl3"
                ],
                "required" => true
            ])
            ->add('lastName', TextType::class, [
                'attr' => [
                    'class' => "size-111 bor8 stext-102 cl2 p-lr-20",
                    "placeholder" => ""
                ],
                'label' => "register.lastName",
                "label_attr" => [
                    'class' => "stext-102 cl3"
                ],
                "required" => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
