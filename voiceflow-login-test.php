<?php

/**
 * VoiceFlow Login Test Script
 *
 * This script demonstrates how to test the VoiceFlow login functionality
 * for schools, doctors, and health facilities.
 */

echo "=== VoiceFlow Login Test Script ===\n\n";

// Test data - replace with actual emails from your database
$testEmails = [
    'school' => 'school@example.com',      // Replace with actual school email
    'doctor' => 'doctor@example.com',      // Replace with actual doctor email
    'health_facility' => 'facility@example.com'  // Replace with actual health facility email
];

echo "Available test emails:\n";
foreach ($testEmails as $type => $email) {
    echo "- $type: $email\n";
}
echo "\n";

echo "=== API Endpoints ===\n";
echo "1. Send Login OTP:\n";
echo "   POST /api/voiceflow/send-login-otp\n";
echo "   Body: {\"email\": \"user@example.com\"}\n\n";

echo "2. Verify OTP:\n";
echo "   POST /api/voiceflow/verify-otp\n";
echo "   Body: {\"email\": \"user@example.com\", \"otp\": \"123456\"}\n\n";

echo "3. Verify Doctor OTP:\n";
echo "   POST /api/voiceflow/verify-doctor-otp\n";
echo "   Body: {\"email\": \"doctor@example.com\", \"otp\": \"123456\"}\n\n";

echo "4. Verify Health Facility OTP:\n";
echo "   POST /api/voiceflow/verify-health-facility-otp\n";
echo "   Body: {\"email\": \"facility@example.com\", \"otp\": \"123456\"}\n\n";

echo "=== Testing Commands ===\n";
echo "Make sure your Laravel server is running on http://localhost:8000\n\n";

echo "1. Test sending OTP to a school:\n";
echo "curl -X POST http://localhost:8000/api/voiceflow/send-login-otp \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"email\":\"{$testEmails['school']}\"}'\n\n";

echo "2. Test verifying OTP:\n";
echo "curl -X POST http://localhost:8000/api/voiceflow/verify-otp \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"email\":\"{$testEmails['school']}\",\"otp\":\"123456\"}'\n\n";

echo "3. Test doctor login:\n";
echo "curl -X POST http://localhost:8000/api/voiceflow/verify-doctor-otp \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"email\":\"{$testEmails['doctor']}\",\"otp\":\"123456\"}'\n\n";

echo "4. Test health facility login:\n";
echo "curl -X POST http://localhost:8000/api/voiceflow/verify-health-facility-otp \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"email\":\"{$testEmails['health_facility']}\",\"otp\":\"123456\"}'\n\n";

echo "=== Expected Responses ===\n";
echo "Success Response:\n";
echo "{\n";
echo "  \"success\": true,\n";
echo "  \"message\": \"OTP verified successfully\",\n";
echo "  \"dashboard_url\": \"https://laravelbackendchil.onrender.com/school-dashboard/1\",\n";
echo "  \"school_id\": 1,\n";
echo "  \"school_name\": \"School Name\"\n";
echo "}\n\n";

echo "Error Response:\n";
echo "{\n";
echo "  \"success\": false,\n";
echo "  \"message\": \"Invalid or expired OTP\"\n";
echo "}\n\n";

echo "=== Notes ===\n";
echo "- OTPs expire after 24 hours\n";
echo "- Each OTP can only be used once\n";
echo "- Check your email for the actual OTP code\n";
echo "- The system supports schools, doctors, and health facilities\n";
echo "- All OTPs are sent via email\n\n";

echo "=== To run the Laravel server ===\n";
echo "php artisan serve --host=0.0.0.0 --port=8000\n\n";

echo "=== To check logs ===\n";
echo "tail -f storage/logs/laravel.log\n\n";

?>