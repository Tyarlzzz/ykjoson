<?php include '../layout/header.php';?>

<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
    <div class="w-full px-8">
        <div class="mb-8 flex justify-between items-center">
            <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Manage Riders</h1>
            <p class="text-gray-500 text-base" id="currentDate"></p>
        </div>

        <div class="flex space-x-2">
            <button class="px-2 bg-gray-200 text-gray-700 font-semibold rounded-t-lg">Inventory</button>
            <button class="px-2 bg-gray-200 text-gray-700 font-semibold rounded-t-lg">Sales Report</button>
            <button class="px-2 bg-red-600 text-gray-700 font-semibold rounded-t-lg">Riders</button> 
            <button class="px-2 bg-gray-200 text-gray-700 font-semibold rounded-t-lg">Archived</button>       
        </div>

        <div class="flex-1 border-t-2 border-gray-200">

        </div>

    </div>
</main>

<script src="../assets/js/gas_system_js/gasPettyCash.js"></script>

<?php include '../layout/footer.php';?>