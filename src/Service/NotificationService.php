<?php

namespace App\Service;

use App\Entity\Subscription as Sub;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class NotificationService {

    public function __construct(
        private string $public_key,
        private string $private_key
    )
    {}

    public function notify( Sub $sub, string $message, string $body ) {

        $subscription = new Subscription(
            $sub->getEndpoint(),
            $sub->getP256dh(),
            $sub->getAuth()
        );

        $webPush = new WebPush($this->getAuth());
        $webPush->setAutomaticPadding(0);

        $report = $webPush->sendOneNotification(
            $subscription,
            json_encode( [ 'message' => $message, 'body' => $body ] )
        );

        $endpoint = $report->getRequest()->getUri()->__toString();

        if ($report->isSuccess()) {
            dump("[v] Message sent successfully for subscription {$endpoint}.");
        } else {
            dump("[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}");
            throw new \Exception("Push notification failed - ".$report->getReason());
        }

    }
    private function getAuth() : array {
        return [
            'VAPID' => [
                'subject' => 'mailto:vapid@example.com',
                'publicKey' => $this->public_key,
                'privateKey' => $this->private_key
            ],
        ];
    }
}