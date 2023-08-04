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
            // Si les données ne sont pas en cache, on effectue le call API et on met en cache les résultats 
            $response = $this->httpClient->request(
                'GET',
                'https://pokebuildapi.fr/api/v1/pokemon'
            );
            $data = $response->toArray();

            $liste = [];

            foreach ($data as $pokemon) {
                $namePokemon = $pokemon['name'];
                $imagePokemon = $pokemon['image'];

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
