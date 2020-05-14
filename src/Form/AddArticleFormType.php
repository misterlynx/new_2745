<?php

namespace App\Form;

use App\Entity\Articles;
use App\Entity\Category;
use App\Entity\Tags;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddArticleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre'
            ])
            ->add('content', CKEditorType::class)
            ->add('featured_image', FileType::class, [
                'label' =>'Image',
            ])
            // ->add('tags', EntityType::class, [
            //     'label' => 'Tags',
            //     'class' => Tags::class,
            //     'multiple' => true,
            //     'expanded' => true,
            // ])
            // ->add('categorys', EntityType::class, [
            //     'label' => 'Catégories',
            //     'class' => Category::class,
            //     'multiple' => true,
            //     'expanded' => true,
            // ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Articles::class,
        ]);
    }
}
