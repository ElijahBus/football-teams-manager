<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamFormType;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Utilities\PaginatorOffsetLimiter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private PlayerRepository $playerRepository;

    private TeamRepository $teamRepository;

    /**
     * @param EntityManagerInterface $entityManager
     * @param PlayerRepository $playerRepository
     * @param TeamRepository $teamRepository
     */
    public function __construct(EntityManagerInterface $entityManager,
                                PlayerRepository       $playerRepository,
                                TeamRepository         $teamRepository)
    {
        $this->entityManager = $entityManager;
        $this->playerRepository = $playerRepository;
        $this->teamRepository = $teamRepository;
    }


    /**
     * @throws Exception
     */
    #[Route('/teams', name: 'list_teams')]
    public function index(Request $request): Response
    {
        [$firstResultStart, $maxPage] = (new PaginatorOffsetLimiter())->getResultRange(
            $request,
            fn() => $this->redirectToRoute('list_teams')
        );

        $query = $this->entityManager
            ->createQuery("SELECT t FROM App\Entity\Team t")
            ->setFirstResult($firstResultStart)
            ->setMaxResults($maxPage);

        $teams = (new Paginator($query, fetchJoinCollection: true))->getIterator();

        return $this->render('team/index.html.twig', [
            'teams' => $teams,
            'currentPage' => $maxPage,
            'perPageItems' => (new PaginatorOffsetLimiter())->getOffset()
        ]);
    }

    #[Route('teams/create', 'create_team')]
    public function create(Request $request): Response
    {
        $form = $this->createForm(TeamFormType::class, new Team());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newTeam = $form->getData();

            foreach ($request->get('players') as $playerId) {
                $newTeam->addPlayer($this->playerRepository->find($playerId));
            }

            $this->teamRepository->save($newTeam, true);

            return $this->redirectToRoute('list_teams');
        }

        $players = $this->playerRepository->findAll();

        return $this->render('team/create.html.twig', [
            'form' => $form->createView(),
            'players' => $players
        ]);
    }

    #[Route('teams/sell-player', 'sell_player', methods: ['GET', 'HEAD'])]
    public function sellPlayer(): Response
    {
        $players = $this->playerRepository->findAll();
        $teams = $this->teamRepository->findAll();

        return $this->render('team/sell_player.html.twig', [
            'players' => $players,
            'teams' => $teams
        ]);
    }

    #[Route('teams/{teamId}/buy-player/{playerId}', methods: ['POST'])]
    public function buyPlayer(Request $request, $teamId, $playerId): Response
    {
        $buyingCost = $request->get('buying_cost');
        $buyingTeamId = $request->get('buying_team');

        $player = $this->playerRepository->find($playerId);
        $sellingTeam = $this->teamRepository->find($teamId);
        $buyingTeam = $this->teamRepository->find($buyingTeamId);

        $sellingTeam->removePlayer($player);
        $buyingTeam->addPlayer($player);

        $buyingTeam->setMoneyBalance($buyingTeam->getMoneyBalance() - (int)$buyingCost);
        $sellingTeam->setMoneyBalance($sellingTeam->getMoneyBalance() + (int)$buyingCost);

        $this->entityManager->flush();

        return $this->redirectToRoute('list_teams');
    }
}
