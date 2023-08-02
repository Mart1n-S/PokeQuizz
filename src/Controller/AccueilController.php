<?php

namespace App\Controller;

use App\Entity\Classement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

class AccueilController extends AbstractController
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {

        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'accueil')]
    public function index(SessionInterface $session, CacheInterface $cache): Response
    {
        $erreur = $session->get('erreurPseudo') === 'pseudoInvalide' ? 'Le pseudo doit être compris entre 1 et 11 caractères.' : null;
        $session->clear();
        $session->set('pseudo', '');

        // Vérifier si les informations sont déjà en cache
        $cachedClassement = $cache->get('classement', function ($item) {
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
