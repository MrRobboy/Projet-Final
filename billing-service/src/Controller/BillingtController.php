<?php

namespace App\Controller;

use App\Entity\Billing;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpClient\HttpClient;

#[Route('/billing', name: 'billing_')]
class BillingController extends AbstractController
{
    #[Route('/read/{id}', name: 'read', methods: ['GET'])]
    public function read($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $billing = $entityManager->getRepository(Billing::class)->find($id);

        if (!$billing) {
            return new JsonResponse(['status' => 'Billing not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $billing->getId(),
            'amount' => $billing->getAmount(),
            'due_date' => $billing->getDueDate()->format('Y-m-d'),
            'customer_email' => $billing->getCustomerEmail(),
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/create', name: 'create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['amount']) || !isset($data['due_date']) || !isset($data['customer_email'])) {
            return new JsonResponse(['status' => 'Missing required data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $billing = new Billing();
        $billing->setAmount($data['amount']);
        $billing->setDueDate(new \DateTime($data['due_date']));
        $billing->setCustomerEmail($data['customer_email']);

        $entityManager->persist($billing);
        $entityManager->flush();

        $notificationUrl = 'http://notification-service.local/notify';
        $client = HttpClient::create();
        $response = $client->request('POST', $notificationUrl, [
            'json' => [
                'sujet' => 'Billing',
                'recipient' => $data['customer_email'],
                'message' => 'Your invoice has been created.'
            ]
        ]);

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 201) {
            return new JsonResponse(['status' => 'Failed to send notification to Notification Service'], $statusCode);
        }

        return new JsonResponse(['status' => 'Billing created and notification sent'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/update/{id}', name: 'update', methods: ['POST'])]
    public function update($id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $billing = $entityManager->getRepository(Billing::class)->find($id);

        if (!$billing) {
            return new JsonResponse(['status' => 'Billing not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Valider les données requises
        if (!isset($data['amount']) || !isset($data['due_date']) || !isset($data['customer_email'])) {
            return new JsonResponse(['status' => 'Missing required data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $billing->setAmount($data['amount']);
        $billing->setDueDate(new \DateTime($data['due_date']));
        $billing->setCustomerEmail($data['customer_email']);

        $entityManager->flush();

        return new JsonResponse(['status' => 'Billing updated!'], JsonResponse::HTTP_OK);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $billing = $entityManager->getRepository(Billing::class)->find($id);

        if (!$billing) {
            return new JsonResponse(['status' => 'Billing not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($billing);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Billing deleted!'], JsonResponse::HTTP_OK);
    }
}
