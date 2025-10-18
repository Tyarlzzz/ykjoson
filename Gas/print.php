<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YK Joson Receipt</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .receipt {
                width: 58mm;
                margin: 0;
                padding: 0;
            }
        }
        
        .receipt {
            width: 58mm;
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.3;
        }
        
        .checkbox {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 2px solid #000;
            margin-right: 5px;
            vertical-align: middle;
        }
        
        .checkbox.checked {
            background-color: #000;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
    </style>
</head>
<body class="bg-gray-100 p-4">
    <div class="flex justify-center mb-4 no-print">
        <button onclick="printReceipt()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
            Print Receipt
        </button>
    </div>
    
    <div class="receipt bg-white mx-auto p-3">
        <!-- Shop Name -->
        <div class="text-center font-bold text-lg mb-2">
            YK JOSON
        </div>
        <div class="text-center text-xs mb-3">
            Laundry & Gasul Services
        </div>
        
        <div class="divider"></div>
        
        <!-- Customer Information -->
        <div class="text-xs mb-2">
            <div class="mb-1"><strong>Customer:</strong> <span id="customerName">Maria Santos</span></div>
            <div class="mb-1"><strong>Phone:</strong> <span id="customerPhone">0912-345-6789</span></div>
            <div class="mb-1"><strong>Address:</strong> <span id="customerAddress">123 Main St, Palayan City</span></div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Delivery Rider -->
        <div class="text-xs mb-3">
            <strong>Delivery Rider:</strong> <span id="deliveryRider">Jose Val Eow</span>
        </div>
        
        <div class="divider"></div>
        
        <!-- Items -->
        <div class="text-xs mb-2">
            <div class="flex font-bold justify-between mb-1">
                <span>Brands</span>
                <span>Quantity</span>
            </div>
            <div class="flex justify-between mb-1">
                <span>Petron</span>
                <span>x <span id="">1</span></span>
            </div>
            <div class="flex justify-between mb-1">
                <span>Econo</span>
                <span>x <span id="econo">1</span></span>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Total Items -->
        <div class="text-xs mb-2">
            <div class="flex justify-between font-bold">
                <span>TOTAL QUANTITY:</span>
                <span><span id="totalQty">2</span> pcs</span>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Total Price -->
        <div class="text-xs mb-3">
            <div class="flex justify-between font-bold">
                <span>TOTAL PRICE:</span>
                <span>₱ <span id="totalPrice">2100.00</span></span>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Note -->
        <div class="text-xs mt-3">
            <div class="font-bold mb-1">Note:</div>
            <div class="italic" id="adminNote">You can deliver it by next week. Thank you!</div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Footer -->
        <div class="text-center text-xs mt-3">
            Thank you for your business!
        </div>
        <div class="text-center text-xs" id="dateTime">
            2024-10-16 14:30:00
        </div>
    </div>

    <script>
        const receiptData = {
            customerName: "Erik Soliman",
            customerPhone: "0912-345-6789",
            customerAddress: "Bukang Liwayway, Bantug",
            deliveryRider: "Charles Jerarld",
            brands: {
                petron: { qty: 2, price: 1200.00 },
                econo: { qty: 1, price: 550.00 },
                seagas: { qty: 3, price: 1800.00 }
            },
            totalQty: 6,
            totalPrice: 3550.00,
            adminNote: "Deliver all brands as ordered."
        };

        // Populate receipt with data
        function populateReceipt() {
            document.getElementById('customerName').textContent = receiptData.customerName;
            document.getElementById('customerPhone').textContent = receiptData.customerPhone;
            document.getElementById('customerAddress').textContent = receiptData.customerAddress;
            document.getElementById('deliveryRider').textContent = receiptData.deliveryRider;

            // Set gas brand quantities and prices
            document.querySelector('span[id=""]').textContent = receiptData.brands.petron.qty;
            document.getElementById('econo').textContent = receiptData.brands.econo.qty;
            // If you add Seagas to HTML, update here as well

            // Set total quantity and price
            document.getElementById('totalQty').textContent = receiptData.totalQty;
            document.getElementById('totalPrice').textContent = receiptData.totalPrice.toFixed(2);
            document.getElementById('adminNote').textContent = receiptData.adminNote;

            // Set current date/time
            const now = new Date();
            const dateTimeString = now.getFullYear() + '-' + 
                                  String(now.getMonth() + 1).padStart(2, '0') + '-' + 
                                  String(now.getDate()).padStart(2, '0') + ' ' +
                                  String(now.getHours()).padStart(2, '0') + ':' + 
                                  String(now.getMinutes()).padStart(2, '0') + ':' + 
                                  String(now.getSeconds()).padStart(2, '0');
            document.getElementById('dateTime').textContent = dateTimeString;
        }

        function printReceipt() {
            window.print();
        }

        // Populate on load
        window.onload = populateReceipt;
    </script>
</body>
</html>