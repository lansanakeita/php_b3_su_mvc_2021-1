<?php

namespace App\Controller;

use App\Entity\User;
use App\Routing\Attribute\Route;
use DateTime;
use Doctrine\ORM\EntityManager;

class IndexController extends AbstractController
{
  #[Route(path: "/", name: "home_page")]
  public function index(EntityManager $em)
  {
    $user = new User();

    $user->setName("Red")
      ->setFirstName("John")
      ->setUsername("Bobby")
      ->setPassword("randompass")
      ->setEmail("bob@bob.com")
      ->setRoles(['ROLE_USER'])
      ->setBirthDate(new DateTime);

    // On demande au gestionnaire d'entités de persister l'objet
    // Attention, à ce moment-là l'objet n'est pas encore enregistré en BDD
    $em->persist($user);
    $em->flush();
  }

  #[Route(path: "/contact", name: "contact", httpMethod: "POST")]
  public function contact()
  {
    echo $this->twig->render('index/contact.html.twig');
  }
}
