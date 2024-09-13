<?php

namespace Tests\Feature\Documents;

use App\Models\DataCollection;
use App\Models\MailTemplate;
use App\User;
use Tests\TestCase;

/**
 *
 */
class IndexTest extends TestCase
{
    /**
     * @var string
     */
    protected string $uri = 'documents';

    /**
     * @var User
     */
    protected User $user;

    /**
     * @var MailTemplate
     */
    protected MailTemplate $mailTemplate;

    /**
     * @var DataCollection
     */
    protected DataCollection $dataCollection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->mailTemplate = MailTemplate::factory()->create(['code' => 'transaction_email_receipt']);
        $this->dataCollection = DataCollection::factory()->create();
    }

    /** @test */
    public function test_if_uri_set(): void
    {
        $this->assertNotEmpty($this->uri);
    }

    /** @test */
    public function test_guest_call(): void
    {
        $response = $this->get($this->uri);

        $response->assertRedirect('/login');
    }

    /** @test */
    public function test_user_call(): void
    {
        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri . '?' . http_build_query([
                'template_code' => $this->mailTemplate->code,
                'data_collection_id' => $this->dataCollection->id,
                'output_format' => 'pdf',
            ]));
        ray($response);

        $response->assertSuccessful();
    }

    /** @test */
    public function test_admin_call(): void
    {
        $this->user->assignRole('admin');

        $this->actingAs($this->user, 'web');

        $response = $this->get($this->uri . '?' . http_build_query([
                'template_code' => $this->mailTemplate->code,
                'data_collection_id' => $this->dataCollection->id,
                'output_format' => 'pdf',
            ]));

        $response->assertSuccessful();
    }
}
