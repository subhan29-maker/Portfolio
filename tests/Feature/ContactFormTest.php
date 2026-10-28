<?php

namespace Tests\Feature;

use App\Mail\ContactMessageReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, string>
     */
    private function validPayload(): array
    {
        return [
            'name' => 'Jane Doe',
            'email' => 'Jane@Example.com',
            'phone' => '+1 555 000 0000',
            'subject' => 'New Laravel application',
            'budget' => '1000-5000',
            'message' => 'I would like to build a Laravel application with a dashboard and reports.',
        ];
    }

    public function test_contact_page_renders_the_form(): void
    {
        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee('<form', false);
        $response->assertSee('name="_token"', false);
        $response->assertSeeText('Send message');
    }

    public function test_a_valid_submission_is_stored_and_confirmed(): void
    {
        $response = $this->post(route('contact.store'), $this->validPayload());

        $response->assertRedirect(route('contact'));
        $response->assertSessionHas('contact_status', 'Thanks, Jane Doe. Your message has been received.');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'New Laravel application',
            'budget' => '1000-5000',
            'notified_owner' => false,
        ]);
    }

    public function test_no_email_is_claimed_when_no_recipient_is_configured(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), $this->validPayload())
            ->assertSessionHas('contact_status', 'Thanks, Jane Doe. Your message has been received.');

        Mail::assertNothingSent();
        $this->assertDatabaseCount('contact_messages', 1);
    }

    public function test_the_owner_is_notified_when_a_recipient_is_configured(): void
    {
        Mail::fake();
        config(['portfolio.contact.notify_email' => 'owner@example.com']);

        $this->post(route('contact.store'), $this->validPayload())
            ->assertSessionHas('contact_status', 'Thanks, Jane Doe. Your message has been received and a notification has been sent.');

        Mail::assertSent(ContactMessageReceived::class, fn ($mail) => $mail->hasTo('owner@example.com'));

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'jane@example.com',
            'notified_owner' => true,
        ]);
    }

    public function test_required_fields_are_validated(): void
    {
        $response = $this->post(route('contact.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_the_message_minimum_length_is_enforced(): void
    {
        $response = $this->post(route('contact.store'), [
            ...$this->validPayload(),
            'message' => 'Too short.',
        ]);

        $response->assertSessionHasErrors('message');
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_an_invalid_phone_number_is_rejected(): void
    {
        $response = $this->post(route('contact.store'), [
            ...$this->validPayload(),
            'phone' => 'call me maybe',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_an_unknown_budget_is_rejected(): void
    {
        $response = $this->post(route('contact.store'), [
            ...$this->validPayload(),
            'budget' => 'a-million',
        ]);

        $response->assertSessionHasErrors('budget');
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_the_honeypot_blocks_spam_without_storing_it(): void
    {
        $response = $this->post(route('contact.store'), [
            ...$this->validPayload(),
            'website' => 'https://spam.example',
        ]);

        $response->assertSessionHasErrors('website');
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_the_contact_form_is_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post(route('contact.store'), $this->validPayload())->assertRedirect(route('contact'));
        }

        $this->post(route('contact.store'), $this->validPayload())->assertStatus(429);

        $this->assertDatabaseCount('contact_messages', 3);
    }
}
