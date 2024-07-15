<?php

namespace App\Controller;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'create_notification', methods: ['POST'])]
    public function createNotification(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $notification = new Notification();
        $notification->setEmailRecipient($data['emailRecipient']);
        $notification->setMessage($data['message']);
        $notification->setSujet($data['sujet']);

        $entityManager->persist($notification);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Notification created!'], JsonResponse::HTTP_CREATED);
    }

    #[Route('/notifications/{id}', name: 'get_notification', methods: ['GET'])]
    public function getNotification($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $notification = $entityManager->getRepository(Notification::class)->find($id);

        if (!$notification) {
            return new JsonResponse(['error' => 'Notification not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $notification->getId(),
            'emailRecipient' => $notification->getEmailRecipient(),
            'sujet' => $notification->getSujet(),
            'message' => $notification->getMessage(),
        ], JsonResponse::HTTP_OK);
    }

    #[Route('/notifications/{id}', name: 'delete_notification', methods: ['DELETE'])]
    public function deleteNotification($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $notification = $entityManager->getRepository(Notification::class)->find($id);

        if (!$notification) {
            return new JsonResponse(['error' => 'Notification not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $entityManager->remove($notification);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Notification deleted'], JsonResponse::HTTP_OK);
    }
}
