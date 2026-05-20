<?php
session_start();
require_once 'db_config.php';

$penalty_id = $_GET['id'];

// 1. Fetch data
$stmt = $pdo->prepare("SELECT sp.amount, u.name, u.email, pt.description 
                       FROM student_penalties sp 
                       JOIN users u ON sp.matric_number = u.user_id 
                       JOIN penalty_types pt ON sp.penalty_type_id = pt.penalty_type_id
                       WHERE sp.penalty_id = ?");
$stmt->execute([$penalty_id]);
$data = $stmt->fetch();

if (!$data) { die("Ledger Record Not Found."); }

// 2. Setup API - CHECK IF YOU ARE USING SANDBOX OR REAL
// If your ToyyibPay dashboard says "Sandbox", use 'dev.toyyibpay.com'
// If it is a real active account, use 'toyyibpay.com'
$baseUrl = 'dev.toyyibpay.com'; 

$post_data = array(
    'userSecretKey' => getenv('TOYYIB_SECRET'),
    'categoryCode'  => getenv('TOYYIB_CAT'),
    'billName'      => 'LesBot Penalty Settlement',
    'billDescription' => $data['description'],
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

// 3. The Handshake
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://$baseUrl/index.php/api/createBill");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data)); // Use http_build_query
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

// 4. LOGIC CHECK
if (isset($result[0]['BillCode'])) {
    $bill_code = $result[0]['BillCode'];
    header("Location: https://$baseUrl/" . $bill_code);
    exit();
} else {
    // If it fails, show the real reason from ToyyibPay
    echo "<h3>NEURAL DIAGNOSTIC FAILURE</h3>";
    echo "Response from Hub: " . $response;
    echo "<br><br>Check your Azure Environment Variables (TOYYIB_SECRET & TOYYIB_CAT)";
    exit();
}