<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    #[Route('/commandes', name: 'create_commande', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getCommande(), true);

        $commande = new Commande();
        $commande->setProductId($data['product_id']);
        $commande->setCustomerEmail($data['customer_email']);
        $commande->setQuantity($data['quantity']);
        $commande->setTotalPrice($data['total_price']);

        $em->persist($commande);
        $em->flush();

        $client = HttpClient::create();
        $response = $client->request('POST', 'http://billing-service.local/create-invoice', [
            'json' => [
                'amount' => $data['total_price'],
                'due_date' => (new \DateTime('+30 days'))->format('Y-m-d'),
                'customer_email' => $data['customer_email']
            ],
        ]);

        if ($response->getStatusCode() !== Response::HTTP_CREATED) {
            return new JsonResponse(['status' => 'Commande created, but failed to create invoice!'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['status' => 'Commande created and invoice created!'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/commandes/{id}', name: 'get_commande', methods: ['GET'])]
    public function read(Commande $commande): JsonResponse
    {
        return $this->json($commande);
    }

    #[Route('/commandes', name: 'list_commandes', methods: ['GET'])]
    public function list(CommandeRepository $commandeRepository): JsonResponse
    {
        $commandes = $commandeRepository->findAll();
        return $this->json($commandes);
    }

    #[Route('/commandes/{id}', name: 'update_commande', methods: ['PUT'])]
    public function update(Request $request, Commande $commande, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getCommande(), true);

        $commande->setProductId($data['product_id'] ?? $commande->getProductId());
        $commande->setCustomerEmail($data['customer_email'] ?? $commande->getCustomerEmail());
        $commande->setQuantity($data['quantity'] ?? $commande->getQuantity());
        $commande->setTotalPrice($data['total_price'] ?? $commande->getTotalPrice());

        $em->flush();

        return new JsonResponse(['status' => 'Commande updated!']);
    }

    #[Route('/commandes/{id}', name: 'delete_commande', methods: ['DELETE'])]
    public function delete(Commande $commande, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($commande);
        $em->flush();

        return new JsonResponse(['status' => 'Commande deleted!']);
    }
}
