<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Receives FedaPay webhook events server-to-server, independently of the
 * donor's browser, so a donation is only ever confirmed once FedaPay itself
 * has validated the payment.
 *
 * Configure in the FedaPay dashboard: Webhooks -> Add endpoint -> URL =
 * https://<your-domain>/api/fedapay-webhook, then copy the signing secret it
 * gives you into FEDAPAY_WEBHOOK_SECRET (.env.local).
 *
 * NOTE: the signature scheme below (`X-FEDAPAY-SIGNATURE: t=<ts>,s=<sig>`,
 * HMAC-SHA256 of "<ts>.<rawBody>") follows FedaPay's documented pattern —
 * double-check it against the current "Webhooks" page in your dashboard
 * before relying on it in production, using FedaPay's "send test event"
 * button and the logged raw payload below to confirm the shape matches.
 */
final class FedaPayWebhookController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/api/fedapay-webhook', name: 'fedapay_webhook', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        $signatureHeader = $request->headers->get('X-FEDAPAY-SIGNATURE');
        $secret = $_ENV['FEDAPAY_WEBHOOK_SECRET'] ?? '';

        if (!$this->isValidSignature($rawBody, $signatureHeader, $secret)) {
            return new JsonResponse(['error' => 'invalid signature'], 401);
        }

        $event = json_decode($rawBody, true);
        if (!is_array($event)) {
            throw new BadRequestHttpException('invalid json');
        }

        $this->logger->info('FedaPay webhook event received', ['event' => $event]);

        $transactionId = $event['entity']['id'] ?? null;
        if (!$transactionId) {
            return new JsonResponse(['received' => true]);
        }

        // Re-fetch the transaction from FedaPay's API rather than trusting the
        // webhook body's amount/status directly — FedaPay's API is the source of truth.
        try {
            $apiResponse = $this->httpClient->request('GET', "https://api.fedapay.com/v1/transactions/{$transactionId}", [
                'headers' => ['Authorization' => 'Bearer ' . ($_ENV['FEDAPAY_SECRET_KEY'] ?? '')],
            ]);
            $body = $apiResponse->toArray(false);
            $transaction = $body['v1/transaction'] ?? $body['transaction'] ?? $body;
        } catch (\Throwable $e) {
            $this->logger->error('FedaPay transaction lookup failed', ['exception' => $e->getMessage()]);
            return new JsonResponse(['error' => 'lookup failed'], 502);
        }

        if (($transaction['status'] ?? null) === 'approved') {
            // TODO: persist the confirmed donation (database, spreadsheet, etc.)
            // and/or send a receipt email here. For now this just logs it.
            $this->logger->info('Donation confirmed via webhook', [
                'id' => $transaction['id'] ?? $transactionId,
                'amount' => $transaction['amount'] ?? null,
                'customer' => $transaction['customer'] ?? null,
            ]);
        } else {
            $this->logger->info('Transaction not approved', [
                'id' => $transactionId,
                'status' => $transaction['status'] ?? null,
            ]);
        }

        return new JsonResponse(['received' => true]);
    }

    private function isValidSignature(string $rawBody, ?string $header, string $secret): bool
    {
        if (!$header || !$secret) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $header) as $segment) {
            [$key, $value] = array_pad(explode('=', trim($segment), 2), 2, null);
            if ($key !== null && $value !== null) {
                $parts[$key] = $value;
            }
        }

        $timestamp = $parts['t'] ?? null;
        $signature = $parts['s'] ?? null;
        if (!$timestamp || !$signature) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$timestamp}.{$rawBody}", $secret);

        return hash_equals($expected, $signature);
    }
}
