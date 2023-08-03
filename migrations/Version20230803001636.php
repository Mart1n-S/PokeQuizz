<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230803001636 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création procédure stockée saveGamePlayer.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE PROCEDURE saveGamePlayer(IN newPseudo VARCHAR(11), IN newScore INT)
        BEGIN
            DECLARE min_score INT;
            DECLARE id_classement INT;
            DECLARE nombre_ligne INT;

            SELECT COUNT(*) INTO nombre_ligne FROM classement;

            IF nombre_ligne >= 20 THEN
                SELECT score, id INTO min_score, id_classement FROM classement WHERE score = (SELECT MIN(score) FROM classement) ORDER BY id DESC LIMIT 1;
        
                IF newScore > min_score THEN
                    UPDATE classement
                    SET pseudo = newPseudo, score = newScore, date = NOW()
                    WHERE id = id_classement;

                    SELECT \'classementUpdate\' AS result;
                ELSE
                    SELECT \'classementNoUpdate\' AS result;
                END IF;
            ELSE
                INSERT INTO classement (pseudo, score, date) VALUES (newPseudo, newScore, NOW());
                SELECT \'classementUpdate\' AS result;
            END IF;
        END;');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP PROCEDURE IF EXISTS saveGamePlayer;');
    }
}
