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

        $webPush = new WebPush($this->getAuth());
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
    private function getAuth() : array {
        return [
            'VAPID' => [
                'subject' => 'https://special-barnacle-wr56qxpjvgf95jw-8000.app.github.dev',
                'publicKey' => 'BLtOgPYtZ3lNa4x6LAHap7MffDMZvDAk6kA9BoUwD50orS4Q2bPNtJ03vTYwVOtFBR404E8WOI7XxSymaZhCsHI',
                'privateKey' => 'NZFNNHfqf8Isxr-NhBCLCzgRwywjpZAk6zZhLNX9J2o',
            ],
        ];
    }
}