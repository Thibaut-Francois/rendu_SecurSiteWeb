<?php

namespace App\Controller;

use App\Entity\Ship;
use App\Form\ShipType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ShipController extends AbstractController
{
    #[Route('/ship', name: 'app_ship')]
    public function index(EntityManagerInterface $em, Request $r, SluggerInterface $slugger): Response
    {        
        $one_ship = new Ship();
        $form = $this->createForm(ShipType::class, $one_ship);

        $form->handleRequest($r);

        if($form->isSubmitted() && $form->isValid()){
            $slug = $slugger->slug($one_ship->getName()).'-'.uniqid();
            $one_ship->setSlug($slug);
            $em->persist($one_ship);
            $em->flush();
        }

        $ships = $em->getRepository(Ship::class)->findAll();

        return $this->render('ship/index.html.twig', [
            'ships' => $ships,
            'add'=> $form->createView()
        ]);
    }

    #[Route('/ship/delete/{id}', name:'app_ship_delete')]
    public function delete(Request $r, EntityManagerInterface $em, Ship $ship){
        if($this->isCsrfTokenValid('delete'.$ship->getId(), $r->request->get('csrf'))){
            $em->remove($ship);
            $em->flush();
        }
        return $this->redirectToRoute('app_ship');
    }

    #[Route('/ship/{slug}', name:'app_ship_show')]
    public function show(Ship $ship){
        return $this->render('ship/show.html.twig', [
            'ship'=> $ship
        ]);
    }

    #[Route('/ship/update/{slug}', name:'app_ship_update')]
    public function update(Ship $ship, Request $r, EntityManagerInterface $em, SluggerInterface $slugger){

        $form = $this->createForm(ShipType::class, $ship);
        $form->handleRequest($r);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($ship->getName()).'-'.uniqid();
            $ship->setSlug($slug);
            $em->persist($ship);
            $em->flush();
            return $this->redirectToRoute('app_ship');
        }

        return $this->render('ship/update.html.twig', [
            'edit' => $form->createView()
        ]);
    }
}
