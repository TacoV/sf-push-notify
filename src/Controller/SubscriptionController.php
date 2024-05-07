<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Form\SubscriptionType;
use App\Repository\SubscriptionRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/subscription')]
class SubscriptionController extends AbstractController
{
    #[Route('/', name: 'app_subscription_index', methods: ['GET'])]
    public function index(SubscriptionRepository $subscriptionRepository): Response
    {
        return $this->render('subscription/index.html.twig', [
            'subscriptions' => $subscriptionRepository->findAll(),
        ]);
    }

    #[Route('/api', name: 'receive_pushsub', methods: ['POST'], format: 'json')]
    public function api(Request $request, EntityManagerInterface $entityManager): Response
    {
        $info = $request->getPayload()->all();

        $subscription = (new Subscription())
            ->setEndpoint($info['endpoint'])
            ->setAuth($info['keys']['auth'])
            ->setP256dh($info['keys']['p256dh']);

        $entityManager->persist($subscription);
        $entityManager->flush();

        return $this->json([]);
    }

    #[Route('/{id}', name: 'app_subscription_delete', methods: ['POST'])]
    public function delete(Request $request, Subscription $subscription, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subscription->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($subscription);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_subscription_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/notify', name: 'app_subscription_notify', methods: ['POST'], format: 'json')]
    public function notify(Request $request, Subscription $subscription, NotificationService $notificationService): Response
    {
        $notificationService->notify(
            $subscription,
            'Notificiation test',
            'Hello world via subscription #'.$subscription->getId(),
        );

        return $this->json([]);
    }
}
