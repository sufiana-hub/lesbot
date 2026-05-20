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

if (!$data) { die("Ledger Record Not Found."); }

// 2. API CONFIGURATION
// If your ToyyibPay account is "Sandbox", use 'dev.toyyibpay.com'
// If it is a real active account, use 'toyyibpay.com'
$baseUrl = 'dev.toyyibpay.com'; 

$post_data = array(
    'userSecretKey' => getenv('TOYYIB_SECRET'),
    'categoryCode'  => getenv('TOYYIB_CAT'),
    'billName'      => 'LesBot Penalty Settlement',
    'billDescription' => 'Ref: ' . $penalty_id . ' | ' . $data['description'],
    'billPriceSetting' => 1,
    'billPayorInfo' => 1,
    'billAmount'    => $data['amount'] * 100, // Cents
    'billReturnUrl' => 'https://lesbot-lestari-bmdahbahbbeeb2f9.southeastasia-01.azurewebsites.net/view_receipt.php',
    'billCallbackUrl' => 'https://lesbot-lestari-bmdahbahbbeeb2f9.southeastasia-01.azurewebsites.net/callback.php',
    'billExternalReferenceNo' => $penalty_id,
    'billTo'        => $data['name'],
    'billEmail'     => $data['email'],
    'billPhone'     => '0112345678',
    'billPaymentChannel' => '0', // 0 = FPX (All Banks)
);  

// 3. NEURAL TRANSMISSION (CURL)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://$baseUrl/index.php/api/createBill");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
// PHP 8.0 automatically closes curl handles, so we remove curl_close to stop the warning.

$result = json_decode($response, true);

// 4. REDIRECTION TO REAL HUB
if (isset($result[0]['BillCode'])) {
    $bill_code = $result[0]['BillCode'];
    header("Location: https://$baseUrl/" . $bill_code);
    exit();
} else {
    echo "<h3>NEURAL GATEWAY ERROR</h3>";
    echo "The Hub responded: " . htmlspecialchars($response);
    echo "<br><br>Action Required: Check Azure Settings for TOYYIB_SECRET and TOYYIB_CAT.";
    exit();
}