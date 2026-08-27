<?php

namespace Tests\Feature;

use Tests\TestCase;

class TrustCenterTest extends TestCase
{
    public function test_the_trust_center_lists_the_planned_public_documents(): void
    {
        $this->get('/trust')
            ->assertOk()
            ->assertSee('Privacy Policy')
            ->assertSee('Terms of Service')
            ->assertSee('Security')
            ->assertSee('Awaiting approved content');
    }

    public function test_unapproved_documents_are_not_published(): void
    {
        $this->get('/legal/privacy')->assertNotFound();
    }

    public function test_approved_documents_expose_versioned_public_content(): void
    {
        config()->set('trust.documents.privacy', [
            'title' => 'Privacy Policy',
            'category' => 'Legal',
            'version' => '1.0',
            'effective_at' => '2026-08-27',
            'published' => true,
            'content' => 'Approved privacy content.',
        ]);

        $this->get('/legal/privacy')
            ->assertOk()
            ->assertSee('Version 1.0')
            ->assertSee('Approved privacy content.');
    }

    public function test_standard_policy_drafts_are_previewable_only_in_the_local_environment(): void
    {
        $this->get('/drafts/legal/refunds')
            ->assertOk()
            ->assertSee('Draft preview')
            ->assertSee('Initial purchases');

        $this->app['env'] = 'production';

        $this->get('/drafts/legal/refunds')->assertNotFound();
    }
}
