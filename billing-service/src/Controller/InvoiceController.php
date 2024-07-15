<?php

namespace App\Controller;

use App\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceController extends AbstractController
{
    #[Route('/invoices', name: 'create_invoice', methods: ['POST'])]
    public function createInvoice(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getCommande(), true);

        $invoice = new Invoice();
        $invoice->setAmount($data['amount']);
        $invoice->setDueDate(new \DateTime($data['dueDate']));
        $invoice->setCustomerEmail($data['customerEmail']);

        $entityManager->persist($invoice);
        $entityManager->flush();

        try {
            $httpClient = HttpClient::create();
            $response = $httpClient->request('POST', 'http://notification-service.local/notifications', [
                'json' => [
                    'sujet' => 'Billing',
                    'emailRecipient' => $data['customerEmail'],
                    'message' => 'Your invoice has been created.'
                ]
            ]);
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse(['error' => 'Failed to notify customer.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['status' => 'Invoice created!'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/invoices/{id}', name: 'get_invoice', methods: ['GET'])]
    public function getInvoice($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $invoice = $entityManager->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            return new JsonResponse(['error' => 'Invoice not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $invoice->getId(),
            'amount' => $invoice->getAmount(),
            'dueDate' => $invoice->getDueDate()->format('Y-m-d'),
            'customerEmail' => $invoice->getCustomerEmail(),
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/invoices/{id}', name: 'delete_invoice', methods: ['DELETE'])]
    public function deleteInvoice($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $invoice = $entityManager->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            return new JsonResponse(['error' => 'Invoice not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($invoice);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Invoice deleted'], JsonResponse::HTTP_OK);
    }
}
