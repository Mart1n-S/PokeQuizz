<?php

namespace App\Controller;

use App\Entity\Classement;
use App\Service\CallPokeApi;
use App\Service\CheckResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Cache\CacheInterface;

class JeuController extends AbstractController
{
    private const MAX_VIES = 3;

    private $callPokeApi;
    private $checkResponse;
    private $dbConnection;
    private $cache;
    private $requestStack;
    private $entityManager;

    public function __construct(CallPokeApi $callPokeApi, CheckResponse $checkResponse, Connection $dbConnection, CacheInterface $cache, RequestStack $requestStack, EntityManagerInterface $entityManager)
    {
        $this->callPokeApi = $callPokeApi;
        $this->checkResponse = $checkResponse;
        $this->dbConnection = $dbConnection;
        $this->cache = $cache;
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
    }

    #[Route('/jeu', name: 'jeu')]
    public function jeu(Request $request): Response
    {
        $pseudo = trim($request->request->get('pseudo'));
        $nomPokemon = $this->requestStack->getSession()->get('nom_pokemon');

        if (!empty($pseudo) && strlen($pseudo) <= 11) {
            if (!$nomPokemon) {
                $randomPokemon =  $this->callPokeApi->getRandomPokemon();
                $pseudo = strip_tags($pseudo);
                $this->requestStack->getSession()->set('pseudo', $pseudo);
                $this->requestStack->getSession()->set('score', 0);
                $this->requestStack->getSession()->set('vie', self::MAX_VIES);
                $this->requestStack->getSession()->set('nom_pokemon', $randomPokemon['name']);
                $this->requestStack->getSession()->set('image_pokemon', $randomPokemon['image']);
                return $this->render('jeu/jeu.html.twig', [
                    'data' => $randomPokemon,
                    'score' => $this->requestStack->getSession()->get('score'),
                    'nombreVies' => self::MAX_VIES,
                    'state' => 'win',
                ]);
            } else {
                return $this->renderJeuView();
            }
        } else {
            $this->requestStack->getSession()->set('erreurPseudo', 'pseudoInvalide');
            return $this->redirectToRoute('accueil');
        }
    }

    #[Route("/verification-reponse", name: "verification_reponse", methods: ["POST"])]
    public function verificationReponse(Request $request): JsonResponse
    {

        $reponseUtilisateur = $this->checkResponse->formatReponse(strip_tags($request->request->get('reponse')));
        $nomPokemon = $this->checkResponse->formatReponse($this->requestStack->getSession()->get('nom_pokemon'));

        $resultat = ($reponseUtilisateur == $nomPokemon);
        $looseContent = null;
        $reponsePokemon = null;
        $score = $this->requestStack->getSession()->get('score');

        if ($resultat) {
            $score++;
            $this->requestStack->getSession()->set('score', $score);
        } else {
            $nombreVie = $this->requestStack->getSession()->get('vie') - 1;
            $this->requestStack->getSession()->set('vie', $nombreVie);
            $reponsePokemon = $this->requestStack->getSession()->get('nom_pokemon');
        }

        $nombreVie = $this->requestStack->getSession()->get('vie');

        if ($nombreVie > 0) {
            $randomPokemon = $this->callPokeApi->getRandomPokemon();
            if (empty($randomPokemon)) {
                $this->requestStack->getSession()->set('winner', 'winGame');
                $winContent = $this->renderView('gameWin/win.html.twig', [
                    'c_js_score' => $score,
                ]);
                return $this->json([
                    'resultat' => 'winGame',
                    'winner' => $winContent,
                ]);
            }
            $this->requestStack->getSession()->set('nom_pokemon', $randomPokemon['name']);
            $this->requestStack->getSession()->set('image_pokemon', $randomPokemon['image']);
        } else {
            $looseContent =  $this->renderView('gameOver/loose.html.twig', [
                'c_js_image' => $this->requestStack->getSession()->get('image_pokemon'),
                'c_js_reponseName' => $this->requestStack->getSession()->get('nom_pokemon'),
                'c_js_scoreFinal' => $score,
            ]);
        }

        $imagePokemon = $this->requestStack->getSession()->get('image_pokemon');

        return $this->json([
            'resultat' => $resultat,
            'image' => $imagePokemon,
            'nombreVie' => $nombreVie,
            'score' => $score,
            'loose' =>  $looseContent,
            'reponsePokemon' => $reponsePokemon,
        ]);
    }

    private function renderJeuView(): Response
    {
        $nombreVie = $this->requestStack->getSession()->get('vie');
        if ($this->requestStack->getSession()->get('winner') === 'winGame') {
            $classSection = 'sectionJeuEnd';
            $state = 'loose';
            return $this->render('jeu/jeu.html.twig', [
                'score' => $this->requestStack->getSession()->get('score'),
                'className' => $classSection,
                'state' => $state,
                'nombreVies' => $nombreVie,
            ]);
        }

        $classSection = ($nombreVie > 0) ? 'sectionJeu' : 'sectionJeuEnd';
        $state = ($nombreVie > 0) ? 'win' : 'loose';

        return $this->render('jeu/jeu.html.twig', [
            'c_image' => $this->requestStack->getSession()->get('image_pokemon'),
            'c_nameReponse' => $this->requestStack->getSession()->get('nom_pokemon'),
            'score' => $this->requestStack->getSession()->get('score'),
            'nombreVies' => $nombreVie,
            'className' => $classSection,
            'state' => $state,
        ]);
    }


    #[Route("/save-data", name: "save_data", methods: ["POST"])]
    public function saveData(): Response
    {
        $pseudo = $this->requestStack->getSession()->get('pseudo');
        $score = $this->requestStack->getSession()->get('score');

        try {
            $this->dbConnection->beginTransaction();
            $this->saveGamePlayer($pseudo, $score);
            $this->dbConnection->commit();
        } catch (\Exception $e) {

            $this->dbConnection->rollBack();
            throw $e;
        }
        return $this->redirectToRoute('accueil');
    }

    private function saveGamePlayer($pseudo, $score)
    {
        try {
            $sql = "CALL saveGamePlayer(:pseudo, :score)";
            $params = [
                'pseudo' => $pseudo,
                'score' => $score,
            ];

            $result = $this->dbConnection->executeQuery($sql, $params)->fetchOne();
            if ($result === 'classementUpdate') {

                $this->cache->delete('classement');

                $repository = $this->entityManager->getRepository(Classement::class);
                $classement = $repository->findBy([], ['score' => 'DESC', 'id' => 'ASC'], null, null, ['pseudo', 'score']);

                $this->cache->get('classement', function ($item) use ($classement) {
                    $item->expiresAfter(86400);
                    return $classement;
                });
            }
        } catch (\Exception $e) {
            $this->dbConnection->rollBack();
            throw $e;
        }
    }
}
