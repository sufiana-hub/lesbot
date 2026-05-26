<?php
/**
 * LESBOT NEURAL GATEWAY
 * VISION: REAL-WORLD FPX INTEGRATION
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

if (!$data) { die("UNAUTHORIZED: Invalid Penalty ID."); }

// 2. REAL-WORLD FEE LOGIC (RM 1.00 Gateway Fee)
$total_to_charge = ($data['amount'] + 1.00) * 100; // API uses cents

// 3. API CONFIGURATION
$url = 'https://toyyibpay.com/index.php/api/createBill'; // Use 'dev.toyyibpay.com' for testing
$post_data = array(
    'userSecretKey' => getenv('TOYYIBPAY_SECRET'),
    'categoryCode'  => getenv('TOYYIBPAY_CAT'),
    'billName'      => 'LesBot Penalty Settlement',
    'billDescription' => 'Ref: ' . $penalty_id . ' | ' . $data['description'],
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount'    => $total_to_charge,
    'billReturnUrl' => 'https://lesbot-lestari-bmdahbahbbeeb2f9.southeastasia-01.azurewebsites.net/view_receipt.php',
    'billCallbackUrl' => 'https://lesbot-lestari-bmdahbahbbeeb2f9.southeastasia-01.azurewebsites.net/callback.php',
    'billExternalReferenceNo' => $penalty_id,
    'billTo'        => $data['name'],
    'billEmail'     => $data['email'],
    'billPhone'     => '01123456789',
    'billPaymentChannel' => '0', // 0 = FPX (The Hub of All Malaysian Banks)
);  

// 4. NEURAL TRANSMISSION (CURL)
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data)); // Professional query encoding
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix for Azure/XAMPP handshake issues

$response = curl_exec($ch);
// We omit curl_close() to fix your PHP 8.5 warning; modern PHP handles this automatically.

$result = json_decode($response, true);

if (isset($result[0]['BillCode'])) {
    // 5. REDIRECT: Send the student to the REAL Bank Hub
    $bill_url = "https://toyyibpay.com/" . $result[0]['BillCode'];
    header("Location: " . $bill_url);
    exit();
} else {
    die("Neural Link Error: The Financial Hub is unreachable. Check Azure Secrets.");
}