<?php

namespace App\Form;

use App\Entity\Player;
use App\Entity\Team;
use App\Repository\PlayerRepository;
use Doctrine\DBAL\Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamFormType extends AbstractType
{
    private PlayerRepository $playerRepository;

    /**
     * @param PlayerRepository $playerRepository
     */
    public function __construct(PlayerRepository $playerRepository)
    {
        $this->playerRepository = $playerRepository;
    }


    /**
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $playersSelection = [];

        $players = $this->playerRepository->findAll();
        array_walk($players, function ($player) use (&$playersSelection) {
            $playersSelection[$player->getName() . ' ' . $player->getSurname()] = $player->getId();
        });

        $builder
            ->add('name', TextType::class, [])
            ->add('country', TextType::class, [])
            ->add('moneyBalance', NumberType::class, []);
//            ->add('players', ChoiceType::class, [
//                'attr' => [
//                    'multiple' => true
//                ],
//                'choices' => $playersSelection,
//            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
