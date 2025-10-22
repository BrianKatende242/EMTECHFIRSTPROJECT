<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use App\Models\School;
use App\Models\Doctor;
use App\Models\HealthFacility;

class VoiceFlowSimulation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'voiceflow:simulate
                            {--type= : User type (school, doctor, health-facility)}
                            {--email= : Email address to test}
                            {--auto-verify : Automatically verify OTP after sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulate VoiceFlow login process for testing purposes';

    /**
     * Base URL for API calls
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost:8000';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎤 VoiceFlow Login Simulation');
        $this->info('==============================');

        // Check if server is running
        if (!$this->isServerRunning()) {
            $this->error('❌ Laravel server is not running on ' . $this->baseUrl);
            $this->info('💡 Start the server with: php artisan serve --host=0.0.0.0 --port=8000');
            return 1;
        }

        $this->info('✅ Server is running');

        // Get user type
        $userType = $this->option('type') ?: $this->choice(
            'Select user type to simulate:',
            ['school', 'doctor', 'health-facility'],
            0
        );

        // Get email
        $email = $this->option('email') ?: $this->ask('Enter email address:');

        if (!$this->validateEmail($email, $userType)) {
            $this->error("❌ Email '{$email}' is not associated with any {$userType}");
            return 1;
        }

        $this->info("📧 Testing {$userType} login for: {$email}");

        // Send OTP
        $this->info('📤 Sending OTP...');
        $otpResponse = $this->sendOtp($email);

        if (!$otpResponse['success']) {
            $this->error('❌ Failed to send OTP: ' . ($otpResponse['message'] ?? 'Unknown error'));
            return 1;
        }

        $this->info('✅ OTP sent successfully!');

        // Get OTP from database for testing
        $otpRecord = DB::table('otps')->where('email', $email)->first();

        if (!$otpRecord) {
            $this->error('❌ Could not retrieve OTP from database');
            return 1;
        }

        $otp = $otpRecord->code;
        $this->info("🔐 OTP Code: {$otp}");

        // Auto-verify if requested
        if ($this->option('auto-verify')) {
            $this->info('🔍 Auto-verifying OTP...');
            $verifyResponse = $this->verifyOtp($email, $otp, $userType);

            if ($verifyResponse['success']) {
                $this->info('✅ OTP verified successfully!');
                if (isset($verifyResponse['login_url'])) {
                    $this->info('🔗 Login URL: ' . $verifyResponse['login_url']);
                } else {
                    $this->info('🏠 Dashboard URL: N/A');
                }
            } else {
                $this->error('❌ OTP verification failed: ' . ($verifyResponse['message'] ?? 'Unknown error'));
            }
        } else {
            // Manual verification
            if ($this->confirm('Would you like to verify the OTP now?')) {
                $enteredOtp = $this->ask('Enter OTP code:');
                $verifyResponse = $this->verifyOtp($email, $enteredOtp, $userType);

                if ($verifyResponse['success']) {
                    $this->info('✅ OTP verified successfully!');
                    if (isset($verifyResponse['login_url'])) {
                        $this->info('🔗 Login URL: ' . $verifyResponse['login_url']);
                    } else {
                        $this->info('🏠 Dashboard URL: N/A');
                    }
                } else {
                    $this->error('❌ OTP verification failed: ' . ($verifyResponse['message'] ?? 'Unknown error'));
                }
            }
        }

        $this->info('🎉 VoiceFlow simulation completed!');
        return 0;
    }

    /**
     * Check if Laravel server is running
     */
    protected function isServerRunning(): bool
    {
        try {
            // Try the root endpoint first (more reliable)
            $response = Http::timeout(5)->get($this->baseUrl);
            return $response->successful();
        } catch (\Exception $e) {
            // Try alternative health check
            try {
                $response = Http::timeout(5)->get($this->baseUrl . '/api/health');
                return $response->successful();
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    /**
     * Validate email exists for the given user type
     */
    protected function validateEmail(string $email, string $userType): bool
    {
        switch ($userType) {
            case 'school':
                return School::where('email', $email)->exists();
            case 'doctor':
                return Doctor::where('email', $email)->exists();
            case 'health-facility':
                return HealthFacility::where('email', $email)->exists();
            default:
                return false;
        }
    }

    /**
     * Send OTP via API
     */
    protected function sendOtp(string $email): array
    {
        try {
            $response = Http::timeout(10)->post($this->baseUrl . '/api/voiceflow/send-login-otp', [
                'email' => $email
            ]);

            return $response->json();
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Request failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify OTP via API
     */
    protected function verifyOtp(string $email, string $otp, string $userType): array
    {
        try {
            $endpoint = match($userType) {
                'school' => '/api/voiceflow/verify-otp',
                'doctor' => '/api/voiceflow/verify-doctor-otp',
                'health-facility' => '/api/voiceflow/verify-health-facility-otp',
                default => '/api/voiceflow/verify-otp'
            };

            $response = Http::timeout(10)->post($this->baseUrl . $endpoint, [
                'email' => $email,
                'otp' => $otp
            ]);

            if ($response->successful()) {
                $json = $response->json();
                return $json ?: ['success' => false, 'message' => 'Invalid JSON response'];
            } else {
                return [
                    'success' => false,
                    'message' => 'HTTP ' . $response->status() . ': ' . $response->body()
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Request failed: ' . $e->getMessage()
            ];
        }
    }
}
