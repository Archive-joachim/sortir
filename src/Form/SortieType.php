<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use App\Entity\Ville;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('dateHeureDebut', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('dateHeureFin', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('dateLimiteInscription', DateTimeType::class, [
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
            ])
            ->add('nbInscriptionsMax')
            ->add('infosSortie', TextareaType::class);
        //on verifie si c est une modif, si c est une modif l entite possede un lieu
        if ($builder->getData()->getLieu()) {
            $builder->add('ville', EntityType::class, [
                'class' => Ville::class,
                'mapped' => false,
                'data' => $builder->getData()->getLieu()->getVille(),
                'placeholder' => 'Choisissez une ville',
                'choice_label' => 'nom',
            ]);
            //si c'est une creation
        } else {
            $builder->add('ville', EntityType::class, [
                'class' => Ville::class,
                'mapped' => false,
                'placeholder' => 'Choisissez une ville',
                'choice_label' => 'nom',
            ]);

        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
