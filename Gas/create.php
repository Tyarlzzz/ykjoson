<?php include '../layout/header.php' ?>

<!-- Main Content Area -->
<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
  <div class="w-full px-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
      <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Add Order</h1>
      <p class="text-gray-500 text-base" id="currentDate"></p>
    </div>

    <form action="store.php" method="POST" id="orderForm">
      <div class="flex gap-8">
        <!-- Left Side ng Form -->
        <div class="flex-1">
          <!-- Customer Information -->
          <div class="mb-6">
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Full Name</label>
                <input type="text" id="fullname" name="fullname" placeholder="Enter full name"
                  class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Number</label>
                <input type="text" id="phone_number" name="phone_number" placeholder="Enter phone number"
                  class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Delivery Address</label>
              <input type="text" id="address" name="address" placeholder="Enter delivery address"
                class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Note</label>
              <textarea id="note" name="note" rows="3" placeholder="Add any special notes..."
                class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 resize-none text-gray-800 font-medium"></textarea>
            </div>
          </div>

          <!-- Brand Selection -->
          <div class="mb-6">
            <div class="flex items-center">
              <h2 class="text-lg font-medium text-gray-700">Brand Selection</h2>
              <div class="flex-1 h-px bg-black ml-4"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
              <!-- Petron -->
              <div
                class="item-card bg-gray-50 text-center border-2 border-transparent hover:border-blue-300 p-4 rounded-xl">
                <h3 class="font-bold text-gray-700 text-base sm:text-lg mb-2">Petron</h3>
                <div
                  class="w-full aspect-square max-w-[120px] sm:max-w-[140px] md:max-w-[176px] mx-auto p-2 sm:p-3 md:p-4 my-2 sm:my-3 md:my-4 border-2 border-gray-200 bg-white shadow-sm rounded-2xl flex items-center justify-center">
                  <img src="../assets/images/petron.png" alt="Petron"
                    class="w-full h-full max-w-[80px] max-h-[80px] sm:max-w-[100px] sm:max-h-[100px] md:max-w-[128px] md:max-h-[128px] object-contain">
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                  <div
                    class="inline-flex border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="petron-qty" class="w-12 text-center font-bold text-gray-700 text-2xl">0</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <button type="button" onclick="decreaseQty('petron')"
                      class="w-10 h-10 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-xl">−</button>
                    <button type="button" onclick="increaseQty('petron')"
                      class="w-10 h-10 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-xl">+</button>
                  </div>
                </div>
              </div>

              <!-- Econo -->
              <div
                class="item-card bg-gray-50 text-center border-2 border-transparent hover:border-blue-300 p-4 rounded-xl">
                <h3 class="font-bold text-gray-700 text-base sm:text-lg mb-2">Econo</h3>
                <div
                  class="w-full aspect-square max-w-[120px] sm:max-w-[140px] md:max-w-[176px] mx-auto p-2 sm:p-3 md:p-4 my-2 sm:my-3 md:my-4 border-2 border-gray-200 bg-white shadow-sm rounded-2xl flex items-center justify-center">
                  <img src="../assets/images/econo.png" alt="Econo"
                    class="w-full h-full max-w-[80px] max-h-[80px] sm:max-w-[100px] sm:max-h-[100px] md:max-w-[128px] md:max-h-[128px] object-contain">
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                  <div
                    class="inline-flex border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="econo-qty" class="w-12 text-center font-bold text-gray-700 text-2xl">0</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <button type="button" onclick="decreaseQty('econo')"
                      class="w-10 h-10 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-xl">−</button>
                    <button type="button" onclick="increaseQty('econo')"
                      class="w-10 h-10 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-xl">+</button>
                  </div>
                </div>
              </div>

              <!-- SeaGas -->
              <div
                class="item-card bg-gray-50 text-center border-2 border-transparent hover:border-blue-300 p-4 rounded-xl">
                <h3 class="font-bold text-gray-700 text-base sm:text-lg mb-2">SeaGas</h3>
                <div
                  class="w-full aspect-square max-w-[120px] sm:max-w-[140px] md:max-w-[176px] mx-auto p-2 sm:p-3 md:p-4 my-2 sm:my-3 md:my-4 border-2 border-gray-200 bg-white shadow-sm rounded-2xl flex items-center justify-center">
                  <img src="../assets/images/seagas.png" alt="SeaGas"
                    class="w-full h-full max-w-[80px] max-h-[80px] sm:max-w-[100px] sm:max-h-[100px] md:max-w-[128px] md:max-h-[128px] object-contain">
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                  <div
                    class="inline-flex border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="seagas-qty" class="w-12 text-center font-bold text-gray-700 text-2xl">0</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <button type="button" onclick="decreaseQty('seagas')"
                      class="w-10 h-10 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-xl">−</button>
                    <button type="button" onclick="increaseQty('seagas')"
                      class="w-10 h-10 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-xl">+</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Order Summary -->
        <div class="w-80">
          <div class="bg-white rounded-2xl shadow-xl p-6 top-8">
            <h2 class="border-b border-black text-xl font-bold text-gray-800 mb-6 pb-2">Order Summary</h2>

            <div class="space-y-3 mb-4 text-sm overflow-y-auto max-h-96">
              <div class="flex justify-between items-start">
                <span class="text-gray-600 flex-shrink-0">Name:</span>
                <div class="flex-1 min-w-0 ml-2 break-words text-right">
                  <span id="summary-name" class="font-semibold text-gray-800 block">
                  </span>
                </div>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-gray-600 flex-shrink-0">Phone Number:</span>
                <div class="flex-1 min-w-0 ml-2 break-words text-right">
                  <span id="summary-phone" class="font-semibold text-gray-800 block">
                  </span>
                </div>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-gray-600 flex-shrink-0">Address:</span>
                <div class="flex-1 min-w-0 ml-2 break-words text-right">
                  <span id="summary-address" class="font-semibold text-gray-800 block">
                  </span>
                </div>
              </div>
            </div>

            <div class="border-t border-black pt-4 mb-4">
              <div class="flex justify-between items-center mb-3 text-sm items-header">
                <span class="text-base font-bold text-gray-700">Brand</span>
                <span class="text-base font-bold text-gray-700">Qty.</span>
              </div>
              <div id="brand-summary" class="space-y-2 text-sm">
                <p class="italic text-gray-500">No items selected</p>
              </div>
            </div>

            <div class="border-t pt-4">
              <div class="flex justify-between items-center mb-2">
                <span class="font-bold text-gray-700">Total Items:</span>
                <span id="total-items" class="font-bold text-xl text-gray-800">3</span>
              </div>
              <div class="text-sm text-gray-600">
                <span class="font-semibold">Notes:</span>
                <p id="summary-notes" class="mt-1 text-gray-500 break-words max-w-full">
              </div>
            </div>
          </div>

          <!-- Action Buttons Outside Container -->
          <div class="flex gap-3 mt-6">
            <button type="button" onclick="cancelOrder()"
              class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-2xl font-semibold transition">
              Cancel
            </button>
            <button type="submit"
              class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-2xl font-semibold flex items-center justify-center gap-2 transition">
              <div class="bg-white rounded-full p-1 flex items-center justify-center">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
              </div>
              <span>Submit</span>
            </button>
          </div>
        </div>
      </div>

      <input type="hidden" id="petron-qty-input" name="petronQty" value="0">
      <input type="hidden" id="econo-qty-input" name="econoQty" value="0">
      <input type="hidden" id="seagas-qty-input" name="seagasQty" value="0">
    </form>
  </div>
</main>

</div> <!-- Close the flex container from header.php -->

<style>
  .item-card {
    transition: all 0.2s ease;
  }

  .item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  }
</style>
<script src="../assets/js/gas_system_js/gasAddOrder.js"></script>

<?php include '../layout/footer.php' ?>