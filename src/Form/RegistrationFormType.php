<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //Ajout du type pour forcer l'utilisateur a rentrer un email
            ->add('email', EmailType::class, [
                'label' => 'register.form.email',
            ])
            ->add('nom', null, [
                'label' => 'register.form.nom',
            ])
            ->add('prenom', null, [
                'label' => 'register.form.prenom',
            ])
            //Demande pour accepter les conditions d'utilisations
            ->add('agreeTerms', CheckboxType::class, [
                'label' => 'register.form.terms.label',
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'register.form.terms.message',
                    ]),
                ],
            ])
            //Gestion du mot de passe pour avoir un champs opérationnel et sécuritaire
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'register.form.password.label',
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'register.form.password.not_blank',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe doit faire {{ limit }} charactères minimum',
                        'max' => 4096,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // set default data class for the form
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
