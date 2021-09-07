<?php


namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Ville;
use App\Repository\SiteRepository;
use App\Repository\VilleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder; //Ici on declare l'encoder qui ve nous permettre d'encoder le password
    private $siteRepository;

    //On instancie l'encoder par injection de dependance dans le constructeur
    public function __construct(UserPasswordEncoderInterface $encoder, SiteRepository $siteRepository) {
        $this->encoder = $encoder;
        $this->siteRepository = $siteRepository;
    }
    public function load(ObjectManager $manager)
    {
        $participants = [
            ['nom' => "Dupont", 'prenom' => "Raymonde", 'telephone' => "0651236987", 'email' => "dupray@gmail.com"],
            ['nom' => "Birou", 'prenom' => "Denis", 'telephone' => "0298705123", 'email' => "denisbibi@yahoo.fr"],
            ['nom' => "Legrand", 'prenom' => "Christiane", 'telephone' => "0658976541", 'email' => "christaine.legrand@orange.fr"],
            ['nom' => "Ledain", 'prenom' => "Kevin", 'telephone' => "0774145897", 'email' => "keke29@gmail.com"],
            ['nom' => "Micoton", 'prenom' => "Mylene", 'telephone' => "0145879652", 'email' => "mymi1547@free.fr"],
        ];
        $faker = \Faker\Factory::create('fr_FR');
        $id=45;
        for ($i = 0; $i < 40; $i++) {
            if($id == 49) $id=45;
            $user = new Participant();
            $user->setPassword($this->encoder->encodePassword($user, "pass"));
            $user->setNom($faker->lastName());
            $user->setPseudo($faker->userName());
            $user->setPrenom($faker->firstName());
            $user->setTelephone($faker->numberBetween(01000000000,06777777777));
            $user->setEmail($faker->freeEmail());
            $user->setSite($this->siteRepository->findOneBy(['id'=>$id]));
            $id ++;
            $manager->persist($user);
        }
        $manager->flush();
    }
}
