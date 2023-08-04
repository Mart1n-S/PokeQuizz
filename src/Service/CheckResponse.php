<?php

namespace App\Service;

class CheckResponse
{

    public function formatReponse(string $str): string
    {
        // Convertir les caractères accentués en leur équivalent sans accent
        $str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
        // Supprimer les caractères qui ne sont pas des lettres ou des chiffres
        $str = preg_replace('/[^a-zA-Z0-9]/', '', $str);
        // Convertir la chaîne en minuscules
        $str = strtolower($str);

        return $str;
    }
}
