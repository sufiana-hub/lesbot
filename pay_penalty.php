<?php
/**
 * LESBOT NEURAL GATEWAY
 * INITIALIZING FPX HANDSHAKE VIA TOYYIBPAY API
 */
session_start();
require_once 'db_config.php';

$penalty_id = $_GET['id'];

// 1. Fetch penalty details for the bill
$stmt = $pdo->prepare("SELECT sp.amount, u.name, u.email, pt.description 
                       FROM student_penalties sp 
                       JOIN users u ON sp.matric_number = u.user_id 
                       JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id
                       WHERE sp.penalty_id = ?");
$stmt->execute([$penalty_id]);
$data = $stmt->fetch();

if (!$data) { die("UNAUTHORIZED: Invalid Penalty ID."); }

// 2. Prepare API Payload
$post_data = array(
    'userSecretKey' => getenv('TOYYIB_SECRET'),
    'categoryCode'  => getenv('TOYYIB_CAT'),
    'billName'      => 'LesBot Penalty Settlement',
    'billDescription' => 'Ref: ' . $penalty_id . ' | ' . $data['description'],
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount'    => $data['amount'] * 100, // API uses cents
    'billReturnUrl' => 'https://lesbot-lestari-bmdahbahbbeeb2f9.southeastasia-01.azurewebsites.net/view_receipt.php',
    'billCallbackUrl' => 'https://lesbot-lestari-bmdahbahbbeeb2f9.southeastasia-01.azurewebsites.net/callback.php',
    'billExternalReferenceNo' => $penalty_id,
    'billTo'        => $data['name'],
    'billEmail'     => $data['email'],
    'billPhone'     => '01123456789',
    'billPaymentChannel' => '0', // 0 = FPX (All Banks across Malaysia)
);  

// 3. Request Secure Bill from ToyyibPay
$url = 'https://toyyibpay.com/index.php/api/createBill';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Crucial for Azure/XAMPP handshake

$response = curl_exec($ch);


$result = json_decode($response, true);

if (isset($result[0]['BillCode'])) {
    // 4. REDIRECT: Send the student to the REAL Bank Hub
    $bill_url = "https://toyyibpay.com/" . $result[0]['BillCode'];
    header("Location: " . $bill_url);
    exit();
} else {
    // Debug info for you if it fails
    die("Neural Link Error: " . ($result['msg'] ?? 'Hub connection failed. Check Azure Secrets.'));
}