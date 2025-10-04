<?php
    require '../layout/header.php';
?>

<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
    <div class="w-full px-6">
        <!-- Top header -->
        <div class="mb-8 flex justify-between items-center">
            <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Petty Cash</h1>
            <p class="text-gray-500 text-base" id="currentDate"></p>
        </div>

        <div class="flex space-x-2">
            <button class="px-4 bg-red-600 text-white font-semibold rounded-t-lg">Rider 1</button>
            <button class="px-4 bg-gray-200 text-gray-700 font-semibold rounded-t-lg">Rider 2</button>
        </div>

        <!-- Two-column layout -->

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-20">
            <!-- Left Section (Calculator) -->

            <div class="lg:col-span-3 bg-white shadow-md rounded-xl p-6 self-start">
                <!-- Input Screen -->
                <div class="bg-gray-100 rounded-xl flex justify-between items-center p-8 mb-6 shadow-[inset_0_2px_4px_rgba(0,0,0,0.2)]">
                    <span class="text-6xl font-bold">₱</span>
                    <input type="text" class="flex-1 ml-2 bg-transparent outline-none text-4xl font-bold text-gray-700 pt-4" id="amountInput" readonly>
                </div>

                <div class="flex justify-end mb-4" id="ClearButton">
                    <button onclick="clearInput()"class="clear-btn">X</button>
                </div>

                <!-- Number Pad -->
                <div class="grid grid-cols-3 gap-4" id="numpad">
                    <?php
                        $numbers = [1,2,3,4,5,6,7,8,9,".", 0];
                        foreach ($numbers as $num) {
                            // For JS: if number is a string, wrap it in quotes
                            $jsNum = is_numeric($num) ? $num : "'$num'";
                            echo "<button onclick='appendNumber($jsNum)' class='bg-gray-200 text-5xl shadow-md text-shadow font-extrabold py-2 rounded-xl hover:bg-gray-300 transition'>$num</button>";
                        }
                    ?>
                    <button type="button" onclick="submitAmount()" class="bg-blue-600 text-2xl font-semibold text-white py-4 rounded-xl hover:bg-blue-400 transition">Submit</button>
                </div>
            </div>

            <!-- Right Section (Summary Card) -->
            <div class="bg-white shadow-md rounded-xl p-6 lg:col-span-2 self-start">
                <h2 class="font-bold text-lg text-center">PETTY CASH</h2>
                <p class="text-black-500 text-base border-b-2 border-gray-500 text-center" id="receiptDate"></p>
                <p class="text-md text-black-800 font-semibold mb-4 text-left">Name: Jose Eowyn Laurente</p>

                <div class="space-y-4 mt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Petty cash given</span>
                    </div>

                    <div class="flex justify-end items-center mb-4">
                        <span id="pettyCashGiven" class="text-5xl font-bold">₱</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Today’s Total Sales</span>
                    </div>

                    <div class="flex justify-end items-center">
                        <span id="sales" class="text-5xl font-bold">₱5,829</span>
                    </div>
                    
                    <div class="flex justify-between items-center border-t-2 border-black pt-4">
                        <span class="text-gray-600 font-semibold">Total Amount</span>
                    </div>
                    <div class="flex justify-end items-center">
                        <p id="totalAmount" class="text-5xl font-extrabold text-black-700">₱7,149</p>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="mt-6 flex gap-4">
                    <button class="flex-1 bg-red-500 text-white py-2 rounded-md font-semibold hover:bg-red-600">Delete</button>
                    <button class="flex-1 bg-green-500 text-white py-2 rounded-md font-semibold hover:bg-green-600">Confirm</button>
                </div>
            </div>

        </div>
    </div>
</main>

<script src="../assets/js/gas_system_js/gasPettyCash.js"></script>

<?php
    require '../layout/footer.php';
?>
