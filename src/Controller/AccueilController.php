<?php

namespace App\Controller;

use App\Entity\Classement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

class AccueilController extends AbstractController
{

    private $entityManager;
    private $requestStack;
    private $cache;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack, CacheInterface $cache)
    {

        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->cache = $cache;
    }

    #[Route('/', name: 'accueil')]
    public function index(): Response
    {
        $erreur = $this->requestStack->getSession()->get('erreurPseudo') === 'pseudoInvalide' ? 'Le pseudo doit être compris entre 1 et 11 caractères.' : null;
        $this->requestStack->getSession()->clear();
        $this->requestStack->getSession()->set('pseudo', '');

        // Vérifier si les informations sont déjà en cache
        $cachedClassement = $this->cache->get('classement', function ($item) {
            // Si les informations ne sont pas en cache, on les récupère depuis la base de données
            $repository = $this->entityManager->getRepository(Classement::class);
            $classement = $repository->findBy([], ['score' => 'DESC', 'id' => 'ASC'], null, null, ['pseudo', 'score']);

            // On met les informations en cache pour une durée donnée (ici, 1 jour)
            $item->expiresAfter(86400); // 1 jour
            return $classement;
        });

        return $this->render('accueil/index.html.twig', [
            'classement' => $cachedClassement,
            'erreur' => $erreur,
        ]);
    }
}
