<?php

namespace App\Form;

use App\Entity\Abonnement;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// This UserType form lets you modify a user's basic data, such as email address, surname and first name. 
// If the user is an administrator (ROLE_ADMIN), the form also lets you change the user's roles between administrator and standard user.
class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // We add the fields we want to display in the form.
        $builder
            ->add('email')
            ->add('Nom')
            ->add('Prenom');

        $user = $options['data']; // We retrieve the user object from the form options.

        // If the user is an administrator, we add the roles field.
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $builder->add('roles', ChoiceType::class, [
                'choices' => [
                    'Admin' => 'ROLE_ADMIN',
                    'Utilisateur' => 'ROLE_USER',
                ],
                // We display the roles as checkboxes.
                'multiple' => true,
                'expanded' => true,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // We configure the form to use the User class.
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
