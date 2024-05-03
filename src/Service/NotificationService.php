<?php

namespace App\Service;

use App\Entity\Subscription as Sub;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class NotificationService {

    public function notify( Sub $sub, string $message ) {

        $subscription = new Subscription(
            $sub->getEndpoint(),
            $sub->getP256dh(),
            $sub->getAuth()
        );

        $webPush = new WebPush();
        $report = $webPush->sendOneNotification(
            $subscription,
            json_encode( [ 'message' => $message ] )
        );

        $endpoint = $report->getRequest()->getUri()->__toString();

        if ($report->isSuccess()) {
            dump("[v] Message sent successfully for subscription {$endpoint}.");
        } else {
            dump("[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}");
        }

    }
}