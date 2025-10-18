<?php
// Configuration
$comPort = "COM9"; // Change this to your Bluetooth COM port

// ESC/POS Commands
define('ESC', "\x1B");
define('GS', "\x1D");

// Initialize
$ESC_INIT = ESC . "@";
$ESC_ALIGN_CENTER = ESC . "a" . chr(1);
$ESC_ALIGN_LEFT = ESC . "a" . chr(0);
$ESC_ALIGN_RIGHT = ESC . "a" . chr(2);
$ESC_BOLD_ON = ESC . "E" . chr(1);
$ESC_BOLD_OFF = ESC . "E" . chr(0);
$ESC_SIZE_NORMAL = GS . "!" . chr(0);
$ESC_SIZE_DOUBLE = GS . "!" . chr(17);
$ESC_CUT = GS . "V" . chr(66) . chr(0);

$message = "";
$messageType = "";

if (isset($_POST['print'])) {
    try {
        // Open COM port
        $fp = fopen($comPort, "w");
        
        if (!$fp) {
            throw new Exception("Cannot open printer on port $comPort");
        }
        
        // Sample customer data
        $customerName = "Danielle Quiambao";
        $customerPhone = "0912-345-6789";
        $customerAddress = "Bukang Liwayway, Bantug SCM";
        $isRushed = true; // Set to true or false
        $riderName = "Erik Soliman";
        
        // Sample items
        $items = [
            ['name' => 'Tops', 'qty' => 5],
            ['name' => 'Bottom', 'qty' => 3],
            ['name' => 'Underwear', 'qty' => 8],
            ['name' => 'Socks', 'qty' => 4]
        ];
        
        $totalQty = 0;
        foreach ($items as $item) {
            $totalQty += $item['qty'];
        }
        
        $totalWeight = 12.5; // in kg
        $totalAmount = 350.00; // in pesos
        $note = "Please wash with cold water";
        
        // Build receipt content
        $receipt = "";
        
        // Initialize printer
        $receipt .= $ESC_INIT;
        
        // Header - Center aligned
        $receipt .= $ESC_ALIGN_CENTER;
        $receipt .= $ESC_SIZE_DOUBLE;
        $receipt .= "YK Joson\n";
        $receipt .= $ESC_SIZE_NORMAL;
        $receipt .= "Laundry and Gasul Services\n";
        $receipt .= "\n";
        $receipt .= "================================\n";
        $receipt .= "\n";
        
        // Date - Left aligned
        $receipt .= $ESC_ALIGN_LEFT;
        $receipt .= "Date: " . date('m/d/Y') . "\n";
        $receipt .= "--------------------------------\n";
        
        // Customer Info
        $receipt .= "Name: " . $customerName . "\n";
        $receipt .= "Phone: " . $customerPhone . "\n";
        $receipt .= "Address: " . $customerAddress . "\n";
        $receipt .= "--------------------------------\n";
        
        // Rushed order checkbox
        $rushedCheck = $isRushed ? "[X]" : "[ ]";
        $receipt .= $rushedCheck . " Rushed Order\n";
        $receipt .= "\n";
        
        // Rider name
        $receipt .= "Rider Name: " . $riderName . "\n";
        $receipt .= "--------------------------------\n";
        $receipt .= "\n";
        
        // Items header
        $receipt .= "Item Name                    Qty\n";
        $receipt .= "--------------------------------\n";
        
        // Print items
        foreach ($items as $item) {
            $namePart = str_pad(substr($item['name'], 0, 25), 25);
            $qtyPart = str_pad($item['qty'], 3, ' ', STR_PAD_LEFT);
            
            $receipt .= $namePart . $qtyPart . "\n";
        }
        
        $receipt .= "--------------------------------\n";
        
        // Total Qty
        $receipt .= "Total Qty:" . str_pad($totalQty, 20, ' ', STR_PAD_LEFT) . "\n";
        
        // Total Weight
        $receipt .= "Total Weight:" . str_pad($totalWeight . " kg", 17, ' ', STR_PAD_LEFT) . "\n";
        
        $receipt .= "================================\n";
        
        // TOTAL (large)
        $receipt .= $ESC_SIZE_DOUBLE;
        $receipt .= "TOTAL: P" . number_format($totalAmount, 2) . "\n";
        $receipt .= $ESC_SIZE_NORMAL;
        $receipt .= "================================\n";
        $receipt .= "\n";
        
        // Note
        $receipt .= "Note:\n";
        $receipt .= $note . "\n";
        $receipt .= "\n";
        
        // Footer - Center aligned
        $receipt .= $ESC_ALIGN_CENTER;
        $receipt .= "Thank You!\n";
        $receipt .= "Please Come Again\n";
        $receipt .= "\n";
        $receipt .= "================================\n";
        $receipt .= "\n\n\n";
        
        // Cut paper
        $receipt .= $ESC_CUT;
        
        // Send to printer
        fwrite($fp, $receipt);
        fclose($fp);
        
        $message = "Receipt printed successfully!";
        $messageType = "success";
        
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YK Joson - Print Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 100px auto;
            text-align: center;
        }
        h1 {
            color: #333;
        }
        button {
            padding: 20px 40px;
            font-size: 18px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #45a049;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .success { 
            background: #d4edda; 
            color: #155724; 
            border: 1px solid #c3e6cb;
        }
        .error { 
            background: #f8d7da; 
            color: #721c24; 
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <h1>YK Joson Laundry</h1>
    <p>58mm Paper (48mm Printable)</p>
    
    <form method="POST">
        <button type="submit" name="print">Print Test Receipt</button>
    </form>
    
    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
</body>
</html>