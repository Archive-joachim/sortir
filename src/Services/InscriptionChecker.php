<?php


namespace App\Services;


use App\Entity\Participant;
use App\Entity\Sortie;

class InscriptionChecker
{
    public function isRegistered(Participant $participant, Sortie $sortie)
    {
        $inscriptions = $sortie->getInscriptions()->getValues();
        foreach ($inscriptions as $inscription) {
            if ($inscription->getParticipant() === $participant) {
                return true;
            };
        };
        return false;
    }
}