<?php
/**
 * LESBOT NEURAL GATEWAY
 * FIXED: AZURE VARIABLE SYNC v3.2
 */
session_start();
require_once 'db_config.php';

$penalty_id = $_GET['id'];

// 1. DATA ACQUISITION
$stmt = $pdo->prepare("SELECT sp.amount, u.name, u.email, pt.description 
                       FROM student_penalties sp 
                       JOIN users u ON sp.matric_number = u.user_id 
                       JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id
                       WHERE sp.penalty_id = ?");
$stmt->execute([$penalty_id]);
$data = $stmt->fetch();

if (!$data) { die("LEDGER_ERROR: Record Not Found."); }

// 2. API CONFIGURATION
// Use 'dev.toyyibpay.com' for Sandbox/Student accounts
$baseUrl = 'dev.toyyibpay.com'; 

$post_data = array(
    'userSecretKey' => getenv('TOYYIB_SECRET'), // MATCHES YOUR AZURE NAME
    'categoryCode'  => getenv('TOYYIB_CAT'),    // MATCHES YOUR AZURE NAME
    'billName'      => 'LesBot Settlement',
    'billDescription' => 'Penalty ID: ' . $penalty_id,
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount'    => $data['amount'] * 100, 
    'billReturnUrl' => 'https://lesbot-lestari-bmdahbahbbeeb2f9.southeastasia-01.azurewebsites.net/view_receipt.php',
    'billCallbackUrl' => 'https://lesbot-lestari-bmdahbahbbeeb2f9.southeastasia-01.azurewebsites.net/callback.php',
    'billExternalReferenceNo' => $penalty_id,
    'billTo'        => $data['name'],
    'billEmail'     => $data['email'],
    'billPhone'     => '0112345678',
    'billPaymentChannel' => '0', 
);  

// 3. NEURAL TRANSMISSION
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://$baseUrl/index.php/api/createBill");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
$result = json_decode($response, true);

// 4. HANDSHAKE VERIFICATION
if (isset($result[0]['BillCode'])) {
    $url = "https://$baseUrl/" . $result[0]['BillCode'];
    header("Location: " . $url); 
    // This line ensures Postman "sees" the link even if it doesn't follow the redirect
    echo "GATEWAY_LINK_GENERATED: " . $url; 
    exit();
} else {
    // If it fails, output the raw error so Postman can show us why
    die("NEURAL_GATEWAY_FAILURE: " . $response);
}