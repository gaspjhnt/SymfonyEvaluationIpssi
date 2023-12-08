<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Nom', null,[
                'label' => 'produit.new.form.nom'
            ])
            ->add('Description', null,[
                'label' => 'produit.new.form.description'
            ])
            ->add('Prix', null,[
                'label' => 'produit.new.form.prix'
            ])
            ->add('Stock', null,[
                'label' => 'produit.new.form.stock'
            ])
            //Champs spécifique pour l'ajout d'image
            ->add('Photo', FileType::class, [
                'label' => 'produit.new.form.photo',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'produit.new.form.mimtype_error',
                    ])
                ],
            ])
            ->add('Envoyer', SubmitType::class, [
                'attr' => ['class' => 'btn btn-dark'],
                'label' => 'produit.new.form.envoyer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
