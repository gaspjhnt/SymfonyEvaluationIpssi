<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

// This ProductType form lets you enter product information such as name, description, price and stock, 
// and also allows you to upload an image of the product, specifying validation constraints on file type and size.
class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Create a form field for each property of the Product entity.
        $builder
            ->add('Nom')
            ->add('Description')
            ->add('Prix')
            ->add('Stock')
            ->add('Photo', FileType::class, [
                'label' => 'Photo (JPG, PNG, GIF)',
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
                        'mimeTypesMessage' => 'Merci de choisir une image valide (jpg, png, gif)',
                    ])
                ],
            ])
            // Create a form field for each property of the Product entity.
            ->add('Envoyer', SubmitType::class, [
                'attr' => ['class' => 'btn btn-dark'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // Configure the type of entity associated with the form.
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
