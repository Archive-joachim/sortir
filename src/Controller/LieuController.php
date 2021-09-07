<?php

namespace App\Controller;

use App\Entity\Lieu;
use App\Entity\Ville;
use App\Repository\LieuRepository;
use App\Repository\VilleRepository;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class LieuController extends Controller
{
    /**
     * @Route("/list-lieu/{id}", name="list_lieu")
     */
    public function listLieu(Request $request, LieuRepository $lieuRepository, Ville $ville)
    {
        $lieux = $lieuRepository->findBy(['ville' => $ville]);
        $encoders = array(new JsonEncoder());
        $normalizer = new ObjectNormalizer();
        $normalizers = array($normalizer);
        $serializer = new Serializer($normalizers, $encoders);
        $data = $serializer->serialize($lieux, 'json', ['attributes' => ['id', 'nom', 'codePostal']]);
        return New JsonResponse($data);
    }

    /**
     * @Route("/info-lieu/{id}", name="info_lieu")
     */
    public function infoLieu(Lieu $lieu)
    {
        $encoders = array(new JsonEncoder());
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getNom();
        });
        $normalizers = array($normalizer);
        $serializer = new Serializer($normalizers, $encoders);
        $data = $serializer->serialize($lieu, 'json', ['attributes' => ['rue', 'latitude', 'longitude','ville'=>['codePostal']]]);
        return New JsonResponse($data);
    }
}
