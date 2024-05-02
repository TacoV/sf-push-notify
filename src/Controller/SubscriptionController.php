<?php

namespace App\Controller;

use App\Entity\Subscription;
use App\Form\SubscriptionType;
use App\Repository\SubscriptionRepository;
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
        $subscription = new Subscription();
        $info = $request->getPayload()->all();
        $subscription->setEndpoint($info['endpoint']);
        $subscription->setAuth($info['keys']['auth']);
        $subscription->setP256dh($info['keys']['p256dh']);
        // Received PushSubscription:  {
        //    "endpoint":"https://updates.push.services.mozilla.com/wpush/v2/gAAAAABmM6Uhpowo584MHQ48_1_Z9IyAoOT6SmEbx4qtgjf4KimrS0PPK4yCMsRNz7gswkLT5vfi0KuCBJiZDphSua9Ty8PK3bVUyi9iIPOtscBgctykUBDLR_18tjloFNGZYM1PgjU9ITsfD7Wa_6MCg6ath0V85dL8N0uaMRlQcAtGV_PPcyQ",
        //    "expirationTime":null,
        //    "keys":{
        //        "auth":"6Y__Rg54X6SsXjQbfkV5ow",
        //        "p256dh":"BMQS7CsUn1PFrl3zY35L0tpQsCM9vLxAVZTAhosZ8o4LqEKveWp0NfIGlPAw9_n5p9eloRPC0myK3Al9bL2mit0"
        //    }
        // }

        $entityManager->persist($subscription);
        $entityManager->flush();

        return $this->redirectToRoute('app_subscription_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/new', name: 'app_subscription_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $subscription = new Subscription();
        $form = $this->createForm(SubscriptionType::class, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subscription);
            $entityManager->flush();

            return $this->redirectToRoute('app_subscription_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('subscription/new.html.twig', [
            'subscription' => $subscription,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_subscription_show', methods: ['GET'])]
    public function show(Subscription $subscription): Response
    {
        return $this->render('subscription/show.html.twig', [
            'subscription' => $subscription,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_subscription_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Subscription $subscription, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SubscriptionType::class, $subscription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_subscription_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('subscription/edit.html.twig', [
            'subscription' => $subscription,
            'form' => $form,
        ]);
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
}
