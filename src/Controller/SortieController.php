<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\SiteRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SortieController extends Controller
{
    private $entityManager;
    private $etatRepository;

    public function __construct(EntityManagerInterface $entityManager, EtatRepository $etatRepository, LieuRepository $lieuRepository)
    {
        $this->entityManager = $entityManager;
        $this->etatRepository = $etatRepository;
        $this->lieuRepository = $lieuRepository;
    }

    /**
     * @Route("/", name="home")
     * @param SortieRepository $sortieRepository
     * @param SiteRepository $siteRepository
     * @param Request $request
     * @return Response
     */
    public function index(SortieRepository $sortieRepository, SiteRepository $siteRepository, Request $request)
    {
        $user = $this->getUser();
        $site = $request->get('site');
        $nom = $request->get('nom_sortie');
        $dateDebut = $request->get('date_debut');
        $dateFin = $request->get('date_fin');
        $organised = $request->get('organised_by_me');
        $registered = $request->get('registered');
        $notRegistered = $request->get('not_registered');
        $passed = $request->get('passed');
        $sorties = $sortieRepository->search($user, $site, $nom, $dateDebut, $dateFin, $organised, $registered, $notRegistered, $passed);
        $sites = $siteRepository->findAll();
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sorties,
            'sites' => $sites,
        ]);
    }

    /**
     * @Route("/ajout-sortie", name="add-sortie")
     */
    public function addSortie(Request $request)
    {
        $sortie = new Sortie();
        $user = $this->getUser();
        $lieu = $this->lieuRepository->findOneBy(['id' => $request->get('lieu')]);
        $form = $this->createForm(SortieType::class, $sortie);
        $sortie->setSite($user->getSite());
        $sortie->setOrganisateur($user);
        $sortie->setLieu($lieu);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->get('create')) {
                $sortie->setEtat($this->etatRepository->findOneBy(['libelle' => 'cr????e']));
                $this->addFlash('success', 'Votre annonce est cr????e !');
            } else if ($request->request->get('publish')) {
                $sortie->setEtat($this->etatRepository->findOneBy(['libelle' => 'ouverte']));
                $this->addFlash('success', 'Votre annonce est publi??e !');
            }
            $this->entityManager->persist($sortie);
            $this->entityManager->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('sortie/ajout-sortie.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/publier-sortie/{id}", name="publish-sortie")
     */
    public function publishSortie(Sortie $sortie)
    {
        if ($sortie->getEtat()->getLibelle() == 'cr????e') {
            $sortie->setEtat($this->etatRepository->findOneBy(['libelle' => 'ouverte']));
            $this->entityManager->flush();
            $this->addFlash('success', 'Votre annonce est publi??e !');
        }
        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/detail-sortie/{id}", name="show-sortie")
     */
    public function showSortie(Sortie $sortie)
    {
        return $this->render('sortie/detail-sortie.html.twig', compact('sortie'));
    }

    /**
     * @Route("/modif-sortie/{id}", name="modif-sortie")
     */
    public function modifSortie(Sortie $sortie, Request $request)
    {
        if ($this->getUser() === $sortie->getOrganisateur() && $sortie->getEtat()->getLibelle() === 'cr????e') {
            $form = $this->createForm(SortieType::class, $sortie);
            $lieu = $this->lieuRepository->findOneBy(['id' => $request->get('lieu')]);
            $form->handleRequest($request);
            if ($lieu) {
                $sortie->setLieu($lieu);
            }
            if ($form->isSubmitted() && $form->isValid()) {
                //Si on clique sur 'enregistrer'
                if ($request->request->get('create')) {
                    $this->addFlash('success', 'Votre sortie est modifi??e !');
                    //Si on clique sur 'supprimer la sortie'
                } else if ($request->request->get('suppress')) {
                    $this->entityManager->remove($sortie); //La sortie est supprimee
                    $this->addFlash('warning', 'Votre sortie est supprim??e !');
                } else if ($request->request->get('publish')) {
                    $sortie->setEtat($this->etatRepository->findOneBy(['libelle' => 'ouverte']));
                    $this->addFlash('success', 'Votre annonce est publi??e !');
                }
                $this->entityManager->flush();
                return $this->redirectToRoute('home');
            }
            return $this->render('sortie/modif-sortie.html.twig', [
                'form' => $form->createView(),
            ]);
        }
        return $this->render('sortie/detail-sortie.html.twig', compact('sortie'));
    }

    /**
     * @Route("/annul-sortie/{id}", name="annul-sortie")
     * @param Sortie $sortie
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param EtatRepository $etatRepository
     * @return RedirectResponse|Response
     */
    public function annulSortie(Sortie $sortie, Request $request)
    {
        //A securiser
		if ($this->getUser() === $sortie->getOrganisateur() && $sortie->getEtat()->getLibelle() === 'ouverte') {
			$motif = $request->get('motif-annul');
			$etat = $this->etatRepository->findOneBy(array('libelle' => 'annul??e'));
			if ($motif) {
				$sortie->setMotif($motif);
				$sortie->setEtat($etat);

				$this->addFlash('warning', 'Votre sortie est annul??e !');
				$this->entityManager->flush();

				return $this->redirectToRoute('home');
			}
		}
        return $this->render('sortie/annul-sortie.html.twig', compact('sortie'));
    }

    /**
     * @Route("/suppr-sortie/{id}", name="suppr-sortie")
     */
    public function supprSortie(Sortie $sortie)
    {
        //A securiser
		if ($this->getUser() === $sortie->getOrganisateur() && $sortie->getEtat()->getLibelle() === 'cr????e') {
			$this->entityManager->remove($sortie);
			$this->addFlash('warning', 'la sortie est supprim??e !');
			$this->entityManager->flush();
		}
        return $this->redirectToRoute('home');
    }
}
