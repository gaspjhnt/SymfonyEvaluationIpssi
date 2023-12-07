<?php

namespace App\Form;

use App\Entity\Panier;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


// This Shopping CartType form is designed to allow the input of the datePurchase and cart status fields, 
// as well as the selection of a user associated with the shopping cart via a drop-down list displaying user identifiers.
class PanierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Create a form field for each property of the Panier entity.
        $builder
            ->add('dateAchat')
            ->add('etat')
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // We define the type of entity associated with the form.
        $resolver->setDefaults([
            'data_class' => Panier::class,
        ]);
    }
}
