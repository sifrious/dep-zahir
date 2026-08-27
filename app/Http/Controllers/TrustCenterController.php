<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class TrustCenterController extends Controller
{
    public function index(): View
    {
        return view('trust.index', [
            'documents' => config('trust.documents'),
        ]);
    }

    public function show(string $document): View
    {
        $documentDefinition = config("trust.documents.{$document}");

        abort_unless($documentDefinition && $documentDefinition['published'], 404);

        return view('trust.show', [
            'document' => $this->withContent($documentDefinition),
            'draft' => false,
        ]);
    }

    public function draft(string $document): View
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $documentDefinition = config("trust.documents.{$document}");

        abort_unless($documentDefinition && isset($documentDefinition['content_path']), 404);

        return view('trust.show', [
            'document' => $this->withContent($documentDefinition),
            'draft' => true,
        ]);
    }

    private function withContent(array $document): array
    {
        $content = isset($document['content_path'])
            ? file_get_contents($document['content_path'])
            : $document['content'];

        $document['content'] = str_replace(
            [
                '{{business_name}}',
                '{{support_email}}',
                '{{policy_effective_at}}',
                '{{initial_refund_days}}',
                '{{renewal_refund_days}}',
                '{{cancellation_effective}}',
                '{{delivery_timing}}',
            ],
            [
                config('business.name') ?: '[BUSINESS NAME REQUIRED]',
                config('business.support_email') ?: '[SUPPORT EMAIL REQUIRED]',
                config('business.policy_effective_at') ?: '[EFFECTIVE DATE REQUIRED]',
                config('business.initial_refund_days') ?: '[DECISION REQUIRED]',
                config('business.renewal_refund_days') ?: '[DECISION REQUIRED]',
                config('business.cancellation_effective') ?: '[DECISION REQUIRED]',
                config('business.delivery_timing') ?: '[DECISION REQUIRED]',
            ],
            $content,
        );

        return $document;
    }
}
