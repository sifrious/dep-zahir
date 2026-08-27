<?php

namespace App\Http\Controllers;

use App\Stripe\StripeEventProcessor;
use App\Stripe\StripeWebhookVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        StripeWebhookVerifier $verifier,
        StripeEventProcessor $processor,
    ): JsonResponse {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if (! is_string($signature)) {
            return response()->json(['message' => 'Missing Stripe signature.'], 400);
        }

        try {
            $event = $verifier->verify($payload, $signature);
        } catch (SignatureVerificationException|UnexpectedValueException) {
            return response()->json(['message' => 'Invalid Stripe webhook.'], 400);
        }

        $processor->process($event, hash('sha256', $payload));

        return response()->json(['received' => true]);
    }
}
