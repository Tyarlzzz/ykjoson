<?php
session_start();

if (!isset($_SESSION['owner_logged_in']) || $_SESSION['owner_logged_in'] !== true) {
    header('Location: ownerAccess.php');
    exit;
}
?>

<?php require '../layout/header.php' ?>

<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
    <div class="w-full">
        <div class="mb-6 flex justify-between items-center">
      <h1 class="font-[Outfit] ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Inventory & Sales Report</h1>
        <div class="flex items-center gap-2">
            <p class="text-gray-500 text-base"><?php echo date('F j, Y'); ?></p>
            <a href="logoutInventorySales.php" class="bg-blue-600 text-white py-1 px-4 rounded-full">Logout</a>
        </div>
        </div>

        <div class="flex mb-0">
            <a href="expenses.php" class="px-8 py-1 bg-blue-600 text-white rounded-t-2xl z-0">Expenses</a>
            <a href="salesReport.php"
                class="px-5 py-1 bg-gray-300 text-gray-700 font-semibold rounded-t-2xl -ml-3 z-0">Sales
                Report</a>
            <a href="pricing.php"
                class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Pricing</a>
            <a href="../Rider/ManageRiders.php"
                class="px-10 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Riders</a>
            <a href="archived.php"
                class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Archived</a>
        </div>

        <div class="w-full bg-white rounded-lg rounded-tl-none shadow-md border border-gray-200 overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-center pb-4">
                    <h2 class="text-3xl font-[Outfit] space-x-2">Weekly Expenses Report&nbsp;&nbsp;<span
                            class="font-[Switzer] text-lg"><?php echo date("F, Y"); ?></span></h2>
                    <button id="createWeekBtn"
                        class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-xl font-['Switzer'] flex items-center gap-2 hover:bg-blue-700 transition-colors">
                        <svg class="w-10 h-10 flex-shrink-0 mt-2" xmlns="http://www.w3.org/2000/svg" version="1.1"
                            viewBox="-5.0 -10.0 110.0 135.0">
                            <path
                                d="m83.602 16.398c-18.5-18.5-48.699-18.5-67.199 0s-18.5 48.699 0 67.199 48.699 18.5 67.199 0c18.5-18.496 18.5-48.699 0-67.199zm-9.1016 37.801h-20.398v20.398h-8.3984v-20.398h-20.301v-8.3984h20.301l-0.003906-20.301h8.3984v20.301h20.301z"
                                fill="white" />
                        </svg>
                        <span class="text-2xl">Create Week Expenses</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-3">
                    <!-- Left side: Week Cards -->
                    <div class="flex flex-col gap-4 pe-4" id="cardsList">
                        <!-- Week cards will be added here -->
                    </div>

                    <!-- Right side: Form (Always visible) -->
                    <div class="bg-[#e7e6e6] rounded-xl shadow-lg p-6">
                        <div class="font-['Outfit'] text-4xl text-center py-4">
                            <span id="weekTitle">Week 1</span>
                            <hr class="border-black m-5">
                        </div>
                        <div class="flex justify-between font-['Outfit'] text-3xl px-10 mb-7">
                            <span>Product</span>
                            <span>Price</span>
                        </div>

                        <div class="productList space-y-3 mb-6">
                            <div class="flex justify-between items-center product-row gap-4 mx-4">
                                <div class="flex items-center gap-3 flex-1">
                                    <button type="button"
                                        class="bg-red-600 text-white font-['Outfit'] rounded-full w-10 h-10 flex items-center justify-center text-2xl remove-btn flex-shrink-0">−</button>
                                    <input type="text" placeholder="Enter product" value=""
                                        class="flex-1 text-lg bg-white border-2 border-gray-300 focus:border-blue-600 focus:outline-none px-4 py-2 rounded-lg transition-colors product-input">
                                </div>
                                <input type="number" placeholder="0.00" value=""
                                    class="w-24 text-lg bg-white border-2 border-gray-300 focus:border-blue-600 focus:outline-none px-4 py-2 rounded-lg text-right transition-colors price-input">
                            </div>
                        </div>

                        <div class="flex justify-end me-4 mb-3">
                            <button id="addProductBtn"
                                class="bg-green-500 text-white w-12 h-12 rounded-full text-3xl font-semibold flex items-center justify-center hover:bg-green-600 transition-colors">
                                +
                            </button>
                        </div>

                        <div class="flex justify-end me-4 gap-3">
                            <button id="cancelBtn"
                                class="px-6 py-2 bg-gray-600 text-white font-semibold rounded-xl hover:bg-gray-700 transition-colors hidden">
                                Cancel
                            </button>
                            <button id="saveExpensesBtn"
                                class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-colors">
                                Save Expenses
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>


<script src="../assets/js/laundry_system_js/addExpense.js"></script>
<?php require '../layout/footer.php' ?>