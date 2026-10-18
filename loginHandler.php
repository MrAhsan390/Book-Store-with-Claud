<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

include("includes/attach.php");

$response = [
    'status' => 'error',
    'message' => 'Invalid email or password.'
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $response['message'] = 'Please enter your email and password.';
    echo json_encode($response);
    exit;
}

$stmt = $conn->prepare(
    "SELECT userid, username, email, password FROM users WHERE email = ?"
);

if (!$stmt) {
    error_log("Login prepare failed: " . $conn->error);
    http_response_code(500);
    $response['message'] = 'A server error occurred. Please try again.';
    echo json_encode($response);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);

    $_SESSION['userid'] = $user['userid'];
    $_SESSION['username'] = $user['username'];

    $response['status'] = 'success';
    $response['message'] = 'Login successful!';
}

$stmt->close();

echo json_encode($response);
exit;
?>