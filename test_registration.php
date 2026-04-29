<?php
$baseURL = 'http://localhost/backend';
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
echo "ChaleDash+ Registration Test\n";
echo ".............\n\n";

# Test 1: Successful registration
$res = post("$baseURL/register.php", [
    'firstName' => 'Elizabeth',
    'lastName' => 'Leiyagu',
    'email' => 'elizabeth.leiyagu@ashesi.edu.gh',
    'phoneNumber' => '0241234567',
    'bankName' => 'Ecobank',
    'bankAccount' => '1234567890',
    'password' => 'SecurePass1',
]);
test('Successful registration', $res['code'] === 201 && $res['body']['success'] === true, $res);

# Duplicate email
$res = post("$baseURL/register.php", [
    'firstName' => 'Mimi',
    'lastName' => 'Kamau',
    'email' => 'elizabeth.leiyagu@ashesi.edu.gh',
    'phoneNumber' => '0249999999',
    'bankName' => 'Ecobank',
    'bankAccount' => '0987654321',
    'password' => 'SecurePass1',
]);
test('Reject duplicate email', $res['code'] === 409, $res);

# Non-Ashesi email
$res = post("$baseURL/register.php", [
    'firstName' => 'Milcah',
    'lastName' => 'Abuk',
    'email' => 'milcah.abuk@gmail.com',
    'phoneNumber' => '0551234567',
    'bankName' => 'Stanbic',
    'bankAccount' => '1122334455',
    'password' => 'SecurePass1',
]);
test('Reject non-Ashesi email', $res['code'] === 422, $res);

# Missing fields
$res = post("$baseURL/register.php", [
    'firstName' => 'Nasieku',
]);
test('Reject missing fields', $res['code'] === 422, $res);

# Short password
$res = post("$baseURL/register.php", [
    'firstName' => 'Nana',
    'lastName' => 'Kweku',
    'email' => 'nana.kweku@ashesi.edu.gh',
    'phoneNumber' => '0271234567',
    'bankName' => 'Fidelity',
    'bankAccount' => '5566778899',
    'password' => '123',
]);
test('Reject short password', $res['code'] === 422, $res);

# Wrong verification
$res = post("$baseURL/verify.php", [
    'email' => 'kofi.mensah@ashesi.edu.gh',
    'code' => '000000',
]);
test('Reject wrong verification code', $res['code'] === 401, $res);

# No record
$res = post("$baseURL/verify.php", [
    'email' => 'nobody@ashesi.edu.gh',
    'code' => '123456',
]);
test('Reject verification for unknown email', $res['code'] === 404, $res);

# Missing verification fields
$res = post("$baseURL/verify.php", [
    'email' => 'kofi.mensah@ashesi.edu.gh',
]);
test('Reject missing verification fields', $res['code'] === 400, $res);

echo "\n............\n";
echo "Results: $passed passed, $failed failed\n";
echo "...............\n";