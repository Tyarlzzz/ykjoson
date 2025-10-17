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
        
        <!-- Rushed Status -->
        <div class="mb-2 flex items-center">
            <span class="checkbox" id="rushedCheckbox"></span>
            <span class="text-xs font-bold">RUSHED ORDER</span>
        </div>
        
        <!-- Delivery Rider -->
        <div class="text-xs mb-3">
            <strong>Delivery Rider:</strong> <span id="deliveryRider">Juan Dela Cruz</span>
        </div>
        
        <div class="divider"></div>
        
        <!-- Items -->
        <div class="text-xs mb-2">
            <div class="font-bold mb-1">ITEMS:</div>
            <div class="flex justify-between mb-1">
                <span>Tops</span>
                <span>x <span id="topsQty">5</span></span>
            </div>
            <div class="flex justify-between mb-1">
                <span>Bottoms</span>
                <span>x <span id="bottomsQty">3</span></span>
            </div>
            <div class="flex justify-between mb-1">
                <span>Underwear</span>
                <span>x <span id="underwearQty">8</span></span>
            </div>
            <div class="flex justify-between mb-1">
                <span>Socks</span>
                <span>x <span id="socksQty">6</span></span>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Total Items -->
        <div class="text-xs mb-2">
            <div class="flex justify-between font-bold">
                <span>TOTAL ITEMS:</span>
                <span><span id="totalItems">22</span> pcs</span>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Total Kilograms -->
        <div class="text-sm mb-2">
            <div class="flex justify-between font-bold">
                <span>TOTAL KG:</span>
                <span><span id="totalKg">8.5</span> kg</span>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Total Price -->
        <div class="text-base mb-3">
            <div class="flex justify-between font-bold">
                <span>TOTAL PRICE:</span>
                <span>₱ <span id="totalPrice">425.00</span></span>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Note -->
        <div class="text-xs mt-3">
            <div class="font-bold mb-1">Note:</div>
            <div class="italic" id="adminNote">Please use fabric conditioner. Fold neatly.</div>
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
        // Predefined data (will be replaced with database data later)
        const receiptData = {
            customerName: "Erik Soliman",
            customerPhone: "0912-345-6789",
            customerAddress: "Bukang Liwayway, Bantug",
            isRushed: true, // Change to false to see empty checkbox
            deliveryRider: "Charles Jerarld",
            items: {
                tops: 5,
                bottoms: 3,
                underwear: 8,
                socks: 6
            },
            totalKg: 8.5,
            totalPrice: 425.00,
            adminNote: "Kulang yung isang pares ng medyas teh"
        };

        // Populate receipt with data
        function populateReceipt() {
            document.getElementById('customerName').textContent = receiptData.customerName;
            document.getElementById('customerPhone').textContent = receiptData.customerPhone;
            document.getElementById('customerAddress').textContent = receiptData.customerAddress;
            
            // Set rushed checkbox
            const checkbox = document.getElementById('rushedCheckbox');
            if (receiptData.isRushed) {
                checkbox.classList.add('checked');
            } else {
                checkbox.classList.remove('checked');
            }
            
            document.getElementById('deliveryRider').textContent = receiptData.deliveryRider;
            
            // Set items
            document.getElementById('topsQty').textContent = receiptData.items.tops;
            document.getElementById('bottomsQty').textContent = receiptData.items.bottoms;
            document.getElementById('underwearQty').textContent = receiptData.items.underwear;
            document.getElementById('socksQty').textContent = receiptData.items.socks;
            
            // Calculate and set total items
            const totalItems = receiptData.items.tops + receiptData.items.bottoms + 
                             receiptData.items.underwear + receiptData.items.socks;
            document.getElementById('totalItems').textContent = totalItems;
            
            document.getElementById('totalKg').textContent = receiptData.totalKg.toFixed(1);
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