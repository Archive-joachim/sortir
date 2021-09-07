<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Sortie;
use App\Repository\InscriptionRepository;
use App\Services\InscriptionChecker;
use Doctrine\ORM\EntityManagerInterface;
use http\Env\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class InscriptionController extends Controller
{
    private $entityManager;
    private $checker;

    function __construct(EntityManagerInterface $entityManager, InscriptionChecker $checker)
    {
        $this->entityManager = $entityManager;
        $this->checker = $checker;
    }

    /**
     * @Route("/inscription/{id}", name="inscription")
     */
    public function inscription(Sortie $sortie)
    {
        $participant = $this->getUSer();
        if($participant === $sortie->getOrganisateur()){
            $this->addFlash('danger', 'L\'organisateur ne peut pas s\inscire à sa propre sortie' );
        }
        else if ($this->checker->isRegistered($participant, $sortie)) {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie');
        } else if (!($sortie->isOpened())) {
            $this->addFlash('danger', 'Cette sortie n\'est pas ouverte aux inscriptions');
        } else if ($sortie->isFull()) {
            $this->addFlash('warning', 'Le nombre de participants maximum est atteint');
        } else {
            $inscription = new Inscription();
            $inscription->setSortie($sortie);
            $inscription->setParticipant($participant);
            $inscription->setDateInscription(new \DateTime());
            $this->entityManager->persist($inscription);
            $this->entityManager->flush();
            $this->addFlash('success', 'Vous êtes inscrit');
        }
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/desinscription/{id}", name="desinscription")
     */
    public function desinscription(Sortie $sortie, InscriptionRepository $inscriptionRepository)
    {
        $participant = $this->getUSer();
        if ($this->checker->isRegistered($participant, $sortie)) {
            $inscription = $inscriptionRepository->findOneBy(['participant' => $participant, 'sortie' => $sortie]);
            $sortie->getInscriptions()->removeElement($inscription);
            $this->entityManager->flush();
            $this->addFlash('success', 'Vous êtes désinscrit');
        }
        return $this->redirectToRoute('home');
    }
}
