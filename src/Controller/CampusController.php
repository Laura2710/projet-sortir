<?php

namespace App\Controller;

use App\Manager\CampusManager;
use App\Repository\CampusRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
class CampusController extends AbstractController
{
    #[Route('/campus', name: 'campus_gerer', methods: ['GET', 'POST'])]
    public function gererCampus(CampusRepository $campusRepository): Response
    {
        $campus = $campusRepository->findAll();
        return $this->render('admin/campus.html.twig', [
            'campus' => $campus
        ]);
    }

    #[Route('/campus/creer', name: 'campus_creer', methods: ['POST'])]
    public function creerCampus(Request $request, CampusManager $campusManager): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!empty($data)) {
            $nom = htmlspecialchars(trim($data['nomCampus']));
            $result = $campusManager->creerCampus($nom);
            return $this->json($result);
        }
        return $this->json(['message' => 'error']);
    }

    #[Route('/campus/supprimer/{id}', name: 'campus_supprimer', requirements: ['id' => '\d+'])]
    public function supprimerCampus(int $id, CampusManager $campusManager): Response
    {
        $result = $campusManager->supprimerCampus($id);
        if ($result['status'] === 'error') {
            $this->addFlash('error', $result['message']);
        } else {
            $this->addFlash('success', $result['message']);
        }
        return $this->redirectToRoute('admin_campus_gerer');
    }
    #[Route('/campus/modifier', name: 'campus_modifier', methods: ['POST'])]
    public function modifierCampus(Request $request, CampusManager $campusManager): Response
    {
        $data = json_decode($request->getContent(), true);
        if (!empty($data)) {
            $result = $campusManager->modifierCampus($data['id'], $data['nomCampus']);
            return $this->json($result);
        }
        return $this->json(['message' => 'error']);
    }


}
