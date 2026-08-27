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
            'document' => $documentDefinition,
        ]);
    }
}
