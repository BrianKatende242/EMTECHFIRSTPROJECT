<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\NewsletterSubscriber;

class NewsletterUnsubscribeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_unsubscribes_via_post_and_is_case_insensitive()
    {
        $emailOriginal = 'User@Example.com';
        $emailLower = 'user@example.com';

        NewsletterSubscriber::create([
            'email' => $emailOriginal,
            'verification_token' => 'tok',
            'verified_at' => now(),
            'is_active' => true,
        ]);

        $this->postJson('/api/newsletter/unsubscribe', ['email' => $emailLower])
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => $emailOriginal,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function it_unsubscribes_via_get_and_renders_html()
    {
        $email = 'get@example.com';

        NewsletterSubscriber::create([
            'email' => $email,
            'verification_token' => 'tok',
            'verified_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->get('/api/newsletter/unsubscribe?email=' . urlencode($email));
        $response->assertStatus(200);
        $response->assertSee("You're unsubscribed", false);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => $email,
            'is_active' => false,
        ]);
    }
}
