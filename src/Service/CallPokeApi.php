<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CallPokeApi
{

    private $httpClient;
    private $cache;
    private $requestStack;

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache, RequestStack $requestStack)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->requestStack = $requestStack;
    }

    public function getListePokemon(): array
    {
        // Utilisation du cache pour récupérer les données si elles sont déjà en cache
        $listePokemon = $this->cache->get('liste_pokemon', function (ItemInterface $item) {
            $item->expiresAfter(86400);

            // Génération 1 = espèces 1 à 151. PokéAPI v2 ne renvoie pas les noms
            // en français dans un seul appel : on interroge chaque espèce.
            // Les requêtes HttpClient étant asynchrones, on les lance toutes
            // puis on lit les réponses (exécution concurrente) pour réduire le
            // temps du premier chargement (résultat ensuite mis en cache).
            $responses = [];
            for ($id = 1; $id <= 151; $id++) {
                $responses[$id] = $this->httpClient->request(
                    'GET',
                    'https://pokeapi.co/api/v2/pokemon-species/' . $id
                );
            }

            $liste = [];

            foreach ($responses as $id => $response) {
                $data = $response->toArray();

                // Nom en français, avec repli sur le nom par défaut (anglais)
                $namePokemon = $data['name'];
                foreach ($data['names'] as $name) {
                    if ($name['language']['name'] === 'fr') {
                        $namePokemon = $name['name'];
                        break;
                    }
                }

                // L'image n'est pas dans la réponse "species" : on reconstruit
                // l'URL de l'artwork officiel à partir de l'id.
                $imagePokemon = 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/' . $id . '.png';

                $liste[] = [
                    'name' => $namePokemon,
                    'image' => $imagePokemon,
                ];
            }

            return $liste;
        });

        return $listePokemon;
    }

    public function getRandomPokemon(): array
    {

        $listePokemon = $this->getListePokemon();

        $indicesUtilises = $this->requestStack->getSession()->get('indices_utilises', []);

        if (count($indicesUtilises) === count($listePokemon)) {
            return [];
        }

        $randomIndex = array_rand($listePokemon);

        while (in_array($randomIndex, $indicesUtilises)) {
            $randomIndex = array_rand($listePokemon);
        }

        $randomPokemon = $listePokemon[$randomIndex];

        $namePokemon = $randomPokemon['name'];
        $imagePokemon = $randomPokemon['image'];

        $indicesUtilises[] = $randomIndex;

        $this->requestStack->getSession()->set('indices_utilises', $indicesUtilises);

        return [
            'name' => $namePokemon,
            'image' => $imagePokemon,
        ];
    }
}
