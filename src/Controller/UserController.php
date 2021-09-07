<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ModProfilType;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/user", name="user_")
 */
class UserController extends Controller
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
    }

    /**
     * @Route("/monProfil", name="monProfil")
     */
    public function monProfil(Request $request, ParticipantRepository $repository)
    {
        $user = $this->getUser();
        $form = $this->createForm(ModProfilType::class, $user);
        $form->handleRequest($request);
        $password = $request->get('mod_profil')['password']['first'];
        if ($form->isSubmitted() && $form->isValid()) {
            if(strlen($user->getTelephone()) >= 10){
                if ($password) {
                    $user->setPassword($this->encoder->encodePassword($user, $password));
                }
                $this->entityManager->flush();
                $this->addFlash('success', 'Profil modifié');
            }else{
                $this->addFlash('warning', 'numéro de téléphone invalide');
            }
        }
        return $this->render(
            'user/monProfil.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="profil")
     */
    public function profil(Request $request, ParticipantRepository $repository, Participant $user)
    {
        if ($user->getEmail() == $request->getSession()->get('_security.last_username')) {
            return $this->redirectToRoute('user_monProfil');
        }
        return $this->render(
            'user/profil.html.twig',
            compact('user')
        );
    }
}
