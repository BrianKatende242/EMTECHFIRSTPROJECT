<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Duration;
use App\Models\Patient;
use App\Models\School;
use App\Services\MarzPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MarzPayTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test environment variables
        config([
            'services.marzpay.base_url' => 'https://wallet.wearemarz.com/api/v1',
            'services.marzpay.api_key' => 'test_api_key',
            'services.marzpay.api_secret' => 'test_api_secret',
            'services.marzpay.auth_header' => 'Basic ' . base64_encode('test_api_key:test_api_secret'),
        ]);
    }

    public function test_can_collect_payment()
    {
        // Mock the HTTP client
        Http::fake([
            'https://wallet.wearemarz.com/api/v1/collect-money' => Http::response([
                'status' => 'success',
                'message' => 'Collection initiated successfully.',
                'data' => [
                    'transaction' => [
                        'uuid' => 'test-uuid-123',
                        'reference' => 'COL001',
                        'status' => 'processing'
                    ]
                ]
            ], 200)
        ]);

        $response = $this->postJson('/test/payments/collect', [
            'amount' => 1000,
            'phone_number' => '+256700000000',
            'description' => 'Test payment'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Payment collection initiated successfully'
                ]);
    }

    public function test_can_send_payment()
    {
        Http::fake([
            'https://wallet.wearemarz.com/api/v1/send-money' => Http::response([
                'status' => 'success',
                'message' => 'Send money initiated successfully.',
                'data' => [
                    'transaction' => [
                        'uuid' => 'send-uuid-123',
                        'reference' => 'SEND001',
                        'status' => 'processing'
                    ]
                ]
            ], 200)
        ]);

        $response = $this->postJson('/test/payments/send', [
            'amount' => 500,
            'phone_number' => '+256700000000',
            'description' => 'Test disbursement'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Payment sent successfully'
                ]);
    }

    public function test_validates_payment_request()
    {
        $response = $this->postJson('/test/payments/collect', [
            'amount' => 100, // Below minimum
            'phone_number' => 'invalid-phone',
        ]);

        $response->assertStatus(422);
    }

    public function test_can_check_payment_status()
    {
        Http::fake([
            'https://wallet.wearemarz.com/api/v1/transactions/test-uuid' => Http::response([
                'status' => 'success',
                'data' => [
                    'transaction' => [
                        'uuid' => 'test-uuid',
                        'status' => 'completed',
                        'amount' => 1000
                    ]
                ]
            ], 200)
        ]);

        $response = $this->getJson('/test/payments/status/test-uuid');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);
    }

    public function test_can_get_account_balance()
    {
        Http::fake([
            'https://wallet.wearemarz.com/api/v1/balance' => Http::response([
                'status' => 'success',
                'data' => [
                    'balance' => [
                        'formatted' => '10,000.00',
                        'raw' => 10000,
                        'currency' => 'UGX'
                    ]
                ]
            ], 200)
        ]);

        $response = $this->getJson('/test/payments/balance');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);
    }

    public function test_handles_collection_webhook()
    {
        $school = School::factory()->create();
        $patient = Patient::factory()->create(['school_id' => $school->id]);
        
        // Create required related records
        $doctor = Doctor::create([
            'school_id' => $school->id,
            'name' => 'Test Doctor',
            'specialization' => 'General',
            'email' => 'doctor@test.com',
            'contact' => '+256700000000'
        ]);
        
        $duration = Duration::create([
            'minutes' => 30,
            'general_price' => 1000,
            'specialist_price' => 1500,
            'type' => 'general',
            'is_active' => true
        ]);
        
        $appointment = Appointment::create([
            'school_id' => $school->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'duration_id' => $duration->id,
            'appointment_time' => now()->addDays(1),
            'reason' => 'Test appointment',
            'status' => 'awaiting_payment',
            'payment_reference' => 'test-uuid'
        ]);

        $webhookPayload = [
            'event_type' => 'collection.completed',
            'transaction' => [
                'uuid' => 'test-uuid',
                'reference' => 'appointment-' . $appointment->id . '-123',
                'status' => 'completed',
                'amount' => 1000
            ]
        ];

        $response = $this->postJson('/marzpay/webhook', $webhookPayload);

        $response->assertStatus(200)
                ->assertSee('Webhook processed successfully');

        // Refresh appointment and check status
        $appointment->refresh();
        $this->assertEquals('confirmed', $appointment->status);
        $this->assertEquals('completed', $appointment->payment_status);
    }

    public function test_handles_failed_collection_webhook()
    {
        $school = School::factory()->create();
        $patient = Patient::factory()->create(['school_id' => $school->id]);
        
        // Create required related records
        $doctor = Doctor::create([
            'school_id' => $school->id,
            'name' => 'Test Doctor',
            'specialization' => 'General',
            'email' => 'doctor@test.com',
            'contact' => '+256700000000'
        ]);
        
        $duration = Duration::create([
            'minutes' => 30,
            'general_price' => 1000,
            'specialist_price' => 1500,
            'type' => 'general',
            'is_active' => true
        ]);
        
        $appointment = Appointment::create([
            'school_id' => $school->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'duration_id' => $duration->id,
            'appointment_time' => now()->addDays(1),
            'reason' => 'Test appointment',
            'status' => 'awaiting_payment',
            'payment_reference' => 'test-uuid'
        ]);

        $webhookPayload = [
            'event_type' => 'collection.failed',
            'transaction' => [
                'uuid' => 'test-uuid',
                'reference' => 'appointment-' . $appointment->id . '-123',
                'status' => 'failed',
                'amount' => 1000
            ]
        ];

        $response = $this->postJson('/marzpay/webhook', $webhookPayload);

        $response->assertStatus(200);

        $appointment->refresh();
        $this->assertEquals('failed', $appointment->payment_status);
    }

    public function test_handles_invalid_webhook_payload()
    {
        $response = $this->postJson('/marzpay/webhook', [
            'invalid' => 'payload'
        ]);

        $response->assertStatus(400)
                ->assertSee('Invalid webhook payload');
    }

    public function test_appointment_checkout_creates_payment_request()
    {
        Http::fake([
            'https://wallet.wearemarz.com/api/v1/collect-money' => Http::response([
                'status' => 'success',
                'message' => 'Collection initiated successfully.',
                'data' => [
                    'transaction' => [
                        'uuid' => 'checkout-uuid-123',
                        'reference' => 'COL001',
                        'status' => 'processing'
                    ]
                ]
            ], 200)
        ]);

        $school = School::factory()->create();
        $patient = Patient::factory()->create([
            'school_id' => $school->id,
            'contact_number' => '+256700000000'
        ]);
        
        // Create required related records
        $doctor = Doctor::create([
            'school_id' => $school->id,
            'name' => 'Test Doctor',
            'specialization' => 'General',
            'email' => 'doctor@test.com',
            'contact' => '+256700000000'
        ]);
        
        $duration = Duration::create([
            'minutes' => 30,
            'general_price' => 1000,
            'specialist_price' => 1500,
            'type' => 'general',
            'is_active' => true
        ]);
        
        $appointment = Appointment::create([
            'school_id' => $school->id,
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'duration_id' => $duration->id,
            'appointment_time' => now()->addDays(1),
            'reason' => 'Test appointment',
            'status' => 'awaiting_payment'
        ]);

        $response = $this->postJson('/appointment/checkout', [
            'appointment_id' => $appointment->id,
            'amount' => 1000,
            'phone_number' => '+256700000000'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Payment request sent. Please approve on your phone.'
                ]);

        $appointment->refresh();
        $this->assertNotNull($appointment->payment_reference);
    }

    public function test_marzpay_service_balance_method()
    {
        $service = app(MarzPayService::class);

        Http::fake([
            'https://wallet.wearemarz.com/api/v1/balance' => Http::response([
                'status' => 'success',
                'data' => [
                    'balance' => [
                        'formatted' => '5,000.00',
                        'raw' => 5000,
                        'currency' => 'UGX'
                    ]
                ]
            ], 200)
        ]);

        $result = $service->getBalance();

        $this->assertEquals('success', $result['status']);
        $this->assertEquals(5000, $result['data']['balance']['raw']);
    }

    public function test_marzpay_service_collect_money_method()
    {
        $service = app(MarzPayService::class);

        Http::fake([
            'https://wallet.wearemarz.com/api/v1/collect-money' => Http::response([
                'status' => 'success',
                'message' => 'Collection initiated successfully.',
                'data' => [
                    'transaction' => [
                        'uuid' => 'collect-uuid-123',
                        'status' => 'processing'
                    ]
                ]
            ], 200)
        ]);

        $data = [
            'amount' => 1000,
            'phone_number' => '+256700000000',
            'country' => 'UG',
            'reference' => 'test-ref-123',
            'description' => 'Test collection'
        ];

        $result = $service->collectMoney($data);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('collect-uuid-123', $result['data']['transaction']['uuid']);
    }

    public function test_marzpay_service_send_money_method()
    {
        $service = app(MarzPayService::class);

        Http::fake([
            'https://wallet.wearemarz.com/api/v1/send-money' => Http::response([
                'status' => 'success',
                'message' => 'Send money initiated successfully.',
                'data' => [
                    'transaction' => [
                        'uuid' => 'send-uuid-123',
                        'status' => 'processing'
                    ]
                ]
            ], 200)
        ]);

        $data = [
            'amount' => 500,
            'phone_number' => '+256700000000',
            'country' => 'UG',
            'reference' => 'test-send-ref-123',
            'description' => 'Test send'
        ];

        $result = $service->sendMoney($data);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals('send-uuid-123', $result['data']['transaction']['uuid']);
    }

    public function test_marzpay_service_handles_api_errors()
    {
        $service = app(MarzPayService::class);

        Http::fake([
            'https://wallet.wearemarz.com/api/v1/balance' => Http::response([
                'status' => 'error',
                'message' => 'Invalid API credentials'
            ], 401)
        ]);

        $this->expectException(\Exception::class);
        $service->getBalance();
    }

    public function test_phone_number_validation_for_payments()
    {
        // Test invalid phone number format
        $response = $this->postJson('/test/payments/collect', [
            'amount' => 1000,
            'phone_number' => '256700000000', // Missing +
        ]);

        $response->assertStatus(422);

        // Test valid phone number format
        Http::fake([
            'https://wallet.wearemarz.com/api/v1/collect-money' => Http::response([
                'status' => 'success',
                'message' => 'Collection initiated successfully.',
                'data' => ['transaction' => ['uuid' => 'test-uuid']]
            ], 200)
        ]);

        $response = $this->postJson('/test/payments/collect', [
            'amount' => 1000,
            'phone_number' => '+256700000000', // Valid format
        ]);

        $response->assertStatus(200);
    }

    public function test_amount_validation_limits()
    {
        // Test amount below minimum
        $response = $this->postJson('/test/payments/collect', [
            'amount' => 100, // Below 500 minimum
            'phone_number' => '+256700000000',
        ]);

        $response->assertStatus(422);

        // Test amount above maximum
        $response = $this->postJson('/test/payments/collect', [
            'amount' => 20000000, // Above 10,000,000 maximum
            'phone_number' => '+256700000000',
        ]);

        $response->assertStatus(422);
    }
}