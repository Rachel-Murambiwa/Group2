<?php
$baseURL = 'http://localhost';
$passed = 0;
$failed = 0;

function post(string $url, array $data): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return [
        'code' => $httpCode,
        'body' => json_decode($response, true)
    ];
}

function test(string $name, bool $passed_condition, array $response): void {
    global $passed, $failed;

    if ($passed_condition) {
        echo "PASS: $name\n";
        $passed++;
    } else {
        echo "FAIL: $name\n";
        echo "Response: " . json_encode($response['body']) . "\n";
        echo "HTTP Code: " . $response['code'] . "\n";
        $failed++;
    }
}

echo "\n.............\n";
echo "ChaleDash+ Registration Test (New Schema)\n";
echo ".............\n\n";

# Test 1: Successful registration
$res = post("$baseURL/register.php", [
    'fullName' => 'Elizabeth Leiyagu',
    'phoneNumber' => '0241234567',
    'password' => 'SecurePass1',
]);
test('Successful registration', $res['code'] === 201 && $res['body']['success'] === true, $res);

# Duplicate phone
$res = post("$baseURL/register.php", [
    'fullName' => 'Mimi Kamau',
    'phoneNumber' => '0241234567',
    'password' => 'SecurePass1',
]);
test('Reject duplicate phone', $res['code'] === 409, $res);

# Missing fields
$res = post("$baseURL/register.php", [
    'fullName' => 'Nasieku',
]);
test('Reject missing fields', $res['code'] === 422, $res);

# Short password
$res = post("$baseURL/register.php", [
    'fullName' => 'Nana Kweku',
    'phoneNumber' => '0271234567',
    'password' => '123',
]);
test('Reject short password', $res['code'] === 422, $res);

# Wrong verification
$res = post("$baseURL/verify.php", [
    'phone' => '0241234567',
    'otp' => '000000',
]);
test('Reject wrong verification code', $res['code'] === 400, $res);

# No record
$res = post("$baseURL/verify.php", [
    'phone' => '0999999999',
    'otp' => '123456',
]);
test('Reject verification for unknown phone', $res['code'] === 404, $res);

# Missing verification fields
$res = post("$baseURL/verify.php", [
    'phone' => '0241234567',
]);
test('Reject missing verification fields', $res['code'] === 422, $res);

echo "\n............\n";
echo "Results: $passed passed, $failed failed\n";
echo "...............\n";