<?php

namespace App\Tests;

use App\Entity\Classement;
use PHPUnit\Framework\TestCase;

class ClassementUnitTest extends TestCase
{
    public function testIsTrue()
    {
        $classement = new Classement;

        $classement->setPseudo('toto')
            ->setScore(34)
            ->setDate(new \DateTime('2023-07-20'));

        $this->assertEquals('toto', $classement->getPseudo());
        $this->assertEquals(34, $classement->getScore());
        $this->assertEquals(new \DateTime('2023-07-20'), $classement->getDate());
    }


    public function testIsFalse()
    {
        $classement = new Classement;

        $classement->setPseudo('toto')
            ->setScore(34)
            ->setDate(new \DateTime('2023-07-20'));

        $this->assertFalse($classement->getPseudo() === 'tata');
        $this->assertFalse($classement->getScore() === 1);
        $this->assertFalse($classement->getDate() === '2023-05-12');
    }

    public function testIsEmpty()
    {
        $classement = new Classement;

        $this->assertEmpty($classement->getPseudo());
        $this->assertEmpty($classement->getScore());
        $this->assertEmpty($classement->getDate());
    }
}
