<?php include '../layout/header.php' ?>

<!-- Main Content Area -->
<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
  <div class="w-full px-1">
    <!-- Header --> <!-- DITO MAY CHANGES -->
    <div class="mb-8 flex justify-between items-center">
      <h1 class="font-[Outfit] ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Add Order</h1>
        <p class="text-gray-500 text-base"><?php echo date('F j, Y'); ?></p>
        
    </div>
    <form action="store.php" method="POST" id="orderForm">
      <div class="flex gap-8">
        <!-- Left Side ng Form -->
        <div class="flex-1">
          <!-- Customer Information -->
          <div class="mb-6">

            <div class="grid grid-cols-6">

              <div class="col-span-5">
                <div class="grid grid-cols-2 gap-4 mb-4">
                  <div>
                    <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Full Name</label>
                    <input type="text" id="fullname" name="fullname" placeholder="Enter full name" 
                      class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none position:relative focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
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
              </div>

              <div class=" ms-4 mb-4 mt-7 col-span-1 flex items-stretch items-center">
                <button id="is_rushed" type="button" class="w-full bg-gray-500 hover:bg-gray-600 text-white px-2 py-2 rounded-xl sm:text-xs md:text-md lg:text-xl text-center font-bold transition-colors duration-200">
                  Rush Order
                </button>
              </div>
            </div>

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Note</label>
              <textarea id="note" name="note" rows="3" placeholder="Add any special notes..."
                class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 resize-none text-gray-800 font-medium"></textarea>
            </div>
          </div>

          <!-- Clothes Selection -->
          <div class="mb-6">
            <div class="flex items-center mb-4">
              <h2 class="text-lg font-medium text-gray-700">Clothes Selection</h2>
              <div class="flex-1 h-px bg-black ml-4"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-1 ">
              <!-- Tops -->
              <div class="item-card bg-gray-50 text-center border-2 border-transparent p-1">
                <h3 class="font-bold text-gray-700 text-lg">Tops</h3>
                <div
                  class="w-full max-w-xs aspect-square mx-auto my-4 p-0 bg-white border-2 border-gray-200 shadow-sm rounded-2xl flex items-center justify-center cursor-pointer"
                  onclick="increaseQty('tops')">
                  <img src="../assets/images/tops.png" alt="Tops" class="w-4/5 h-4/5 object-contain">
                </div>
                <div class="flex items-center justify-center gap-1">
                  <div class="inline-flex overflow-hidden border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="tops-qty" contenteditable="true" class="w-12 text-center font-bold text-gray-700 text-xl focus:outline-none"
                      onfocus="this.classList.add('bg-gray-100')"
                      onblur="this.classList.remove('bg-gray-100'); handleQtyChange('tops')">0</span>
                  </div>
                  <button type="button" onclick="decreaseQty('tops')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">−</button>
                  <button type="button" onclick="increaseQty('tops')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">+</button>
                </div>
              </div>

              <!-- Bottoms -->
              <div class="item-card bg-gray-50 text-center border-2 border-transparent p-1">
                <h3 class="font-bold text-gray-700 text-lg">Bottoms</h3>
                <div
                  class="w-full max-w-xs aspect-square mx-auto my-4 p-0 bg-white border-2 border-gray-200 shadow-sm rounded-2xl flex items-center justify-center cursor-pointer"
                  onclick="increaseQty('bottoms')">
                  <img src="../assets/images/bottoms.png" alt="Bottoms" class="w-4/5 h-4/5 object-contain">
                </div>
                <div class="flex items-center justify-center gap-1">
                  <div class="inline-flex overflow-hidden border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="bottoms-qty" contenteditable="true" class="w-12 text-center font-bold text-gray-700 text-xl focus:outline-none"
                      onfocus="this.classList.add('bg-gray-100')"
                      onblur="this.classList.remove('bg-gray-100'); handleQtyChange('bottoms')">0</span>
                  </div>
                  <button type="button" onclick="decreaseQty('bottoms')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">−</button>
                  <button type="button" onclick="increaseQty('bottoms')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">+</button>
                </div>
              </div>

              <!-- Underwears -->
              <div class="item-card bg-gray-50 text-center border-2 border-transparent p-1">
                <h3 class="font-bold text-gray-700 text-lg">Underwears</h3>
                <div
                  class="w-full max-w-xs aspect-square mx-auto my-4 p-0 bg-white border-2 border-gray-200 shadow-sm rounded-2xl flex items-center justify-center cursor-pointer"
                  onclick="increaseQty('undies')">
                  <img src="../assets/images/undies.png" alt="Undies" class="w-4/5 h-4/5 object-contain">
                </div>
                <div class="flex items-center justify-center gap-1">
                  <div class="inline-flex overflow-hidden border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="undies-qty" contenteditable="true" class="w-12 text-center font-bold text-gray-700 text-xl focus:outline-none"
                      onfocus="this.classList.add('bg-gray-100')"
                      onblur="this.classList.remove('bg-gray-100'); handleQtyChange('undies')">0</span>
                  </div>
                  <button type="button" onclick="decreaseQty('undies')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">−</button>
                  <button type="button" onclick="increaseQty('undies')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">+</button>
                </div>
              </div>

              <!-- Socks -->
              <div class="item-card bg-gray-50 text-center border-2 border-transparent p-1">
                <h3 class="font-bold text-gray-700 text-lg">Socks (pair)</h3>
                <div
                  class="w-full max-w-xs aspect-square mx-auto my-4 p-0 bg-white border-2 border-gray-200 shadow-sm rounded-2xl flex items-center justify-center cursor-pointer"
                  onclick="increaseQty('socks')">
                  <img src="../assets/images/socks.png" alt="Socks" class="w-4/5 h-4/5 object-contain">
                </div>
                <div class="flex items-center justify-center gap-1">
                  <div class="inline-flex overflow-hidden border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="socks-qty" contenteditable="true" class="w-12 text-center font-bold text-gray-700 text-xl focus:outline-none"
                      onfocus="this.classList.add('bg-gray-100')"
                      onblur="this.classList.remove('bg-gray-100'); handleQtyChange('socks')">0</span>
                  </div>
                  <button type="button" onclick="decreaseQty('socks')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">−</button>
                  <button type="button" onclick="increaseQty('socks')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">+</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Others Selection -->
          <div class="mt-6 mb-6">
            <div class="flex items-center mb-4">
              <h2 class="text-lg font-medium text-gray-700">Others </h2>
              <div class="flex-1 h-px bg-black ml-4"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-1 ">
              <!-- Towels -->
              <div class="item-card bg-gray-50 text-center border-2 border-transparent p-1">
                <h3 class="font-bold text-gray-700 text-lg">Towels</h3>
                <div
                  class="w-full max-w-xs aspect-square mx-auto my-4 p-0 bg-white border-2 border-gray-200 shadow-sm rounded-2xl flex items-center justify-center cursor-pointer"
                  onclick="increaseQty('towels')">
                  <img src="../assets/images/towels.png" alt="Towels" class="w-4/5 h-4/5 object-contain">
                </div>
                <div class="flex items-center justify-center gap-1">
                  <div class="inline-flex overflow-hidden border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="towels-qty" contenteditable="true" class="w-12 text-center font-bold text-gray-700 text-xl focus:outline-none"
                      onfocus="this.classList.add('bg-gray-100')"
                      onblur="this.classList.remove('bg-gray-100'); handleQtyChange('towels')">0</span>
                  </div>
                  <button type="button" onclick="decreaseQty('towels')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">−</button>
                  <button type="button" onclick="increaseQty('towels')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">+</button>
                </div>
              </div>

              <!-- Bedsheets -->
              <div class="item-card bg-gray-50 text-center border-2 border-transparent p-1">
                <h3 class="font-bold text-gray-700 text-lg">Bedsheets</h3>
                <div
                  class="w-full max-w-xs aspect-square mx-auto my-4 p-0 bg-white border-2 border-gray-200 shadow-sm rounded-2xl flex items-center justify-center cursor-pointer"
                  onclick="increaseQty('beds')">
                  <img src="../assets/images/beds.png" alt="Beds" class="w-4/5 h-4/5 object-contain">
                </div>
                <div class="flex items-center justify-center gap-1">
                  <div class="inline-flex overflow-hidden border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="beds-qty" contenteditable="true" class="w-12 text-center font-bold text-gray-700 text-xl focus:outline-none"
                      onfocus="this.classList.add('bg-gray-100')"
                      onblur="this.classList.remove('bg-gray-100'); handleQtyChange('beds')">0</span>
                  </div>
                  <button type="button" onclick="decreaseQty('beds')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">−</button>
                  <button type="button" onclick="increaseQty('beds')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">+</button>
                </div>
              </div>

              <!-- Gowns -->
              <div class="item-card bg-gray-50 text-center border-2 border-transparent p-1">
                <h3 class="font-bold text-gray-700 text-lg">Gowns</h3>
                <div
                  class="w-full max-w-xs aspect-square mx-auto my-4 p-0 bg-white border-2 border-gray-200 shadow-sm rounded-2xl flex items-center justify-center cursor-pointer"
                  onclick="increaseQty('gowns')">
                  <img src="../assets/images/gowns.png" alt="Gowns" class="w-4/5 h-4/5 object-contain">
                </div>
                <div class="flex items-center justify-center gap-1">
                  <div class="inline-flex overflow-hidden border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="gowns-qty" contenteditable="true" class="w-12 text-center font-bold text-gray-700 text-xl focus:outline-none"
                      onfocus="this.classList.add('bg-gray-100')"
                      onblur="this.classList.remove('bg-gray-100'); handleQtyChange('gowns')">0</span>
                  </div>
                  <button type="button" onclick="decreaseQty('gowns')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">−</button>
                  <button type="button" onclick="increaseQty('gowns')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">+</button>
                </div>
              </div>

              <!-- Barong -->
              <div class="item-card bg-gray-50 text-center border-2 border-transparent p-1">
                <h3 class="font-bold text-gray-700 text-lg">Barong</h3>
                <div
                  class="w-full max-w-xs aspect-square mx-auto my-4 p-0 bg-white border-2 border-gray-200 shadow-sm rounded-2xl flex items-center justify-center cursor-pointer"
                  onclick="increaseQty('barong')">
                  <img src="../assets/images/barong.png" alt="Barong" class="w-4/5 h-4/5 object-contain">
                </div>
                <div class="flex items-center justify-center gap-1">
                  <div class="inline-flex overflow-hidden border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="barong-qty" contenteditable="true" class="w-12 text-center font-bold text-gray-700 text-xl focus:outline-none"
                      onfocus="this.classList.add('bg-gray-100')"
                      onblur="this.classList.remove('bg-gray-100'); handleQtyChange('barong')">0</span>
                  </div>
                  <button type="button" onclick="decreaseQty('barong')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">−</button>
                  <button type="button" onclick="increaseQty('barong')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">+</button>
                </div>
              </div>
            </div>

            <!-- SECOND ROW -->
            <div class=" mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-1 ">
              <!-- Curtains -->
              <div class="item-card bg-gray-50 text-center border-2 border-transparent p-1">
                <h3 class="font-bold text-gray-700 text-lg">Curtains</h3>
                <div
                  class="w-full max-w-xs aspect-square mx-auto my-4 p-0 bg-white border-2 border-gray-200 shadow-sm rounded-2xl flex items-center justify-center cursor-pointer"
                  onclick="increaseQty('curtains')">
                  <img src="../assets/images/curtains.png" alt="Curtains" class="w-4/5 h-4/5 object-contain">
                </div>
                <div class="flex items-center justify-center gap-1">
                  <div class="inline-flex overflow-hidden border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="curtains-qty" contenteditable="true" class="w-12 text-center font-bold text-gray-700 text-xl focus:outline-none"
                      onfocus="this.classList.add('bg-gray-100')"
                      onblur="this.classList.remove('bg-gray-100'); handleQtyChange('curtains')">0</span>
                  </div>
                  <button type="button" onclick="decreaseQty('curtains')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">−</button>
                  <button type="button" onclick="increaseQty('curtains')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">+</button>
                </div>
              </div>

              <!-- Comforter -->
              <div class="item-card bg-gray-50 text-center border-2 border-transparent p-1">
                <h3 class="font-bold text-gray-700 text-lg">Comforter</h3>
                <div
                  class="w-full max-w-xs aspect-square mx-auto my-4 p-0 bg-white border-2 border-gray-200 shadow-sm rounded-2xl flex items-center justify-center cursor-pointer"
                  onclick="increaseQty('comforter')">
                  <img src="../assets/images/comforter.png" alt="Comforter" class="w-4/5 h-4/5 object-contain">
                </div>
                <div class="flex items-center justify-center gap-1">
                  <div class="inline-flex overflow-hidden border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="comforter-qty" contenteditable="true" class="w-12 text-center font-bold text-gray-700 text-xl focus:outline-none"
                      onfocus="this.classList.add('bg-gray-100')"
                      onblur="this.classList.remove('bg-gray-100'); handleQtyChange('comforter')">0</span>
                  </div>
                  <button type="button" onclick="decreaseQty('comforter')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">−</button>
                  <button type="button" onclick="increaseQty('comforter')"
                    class="w-10 h-10 sm:w-12 sm:h-12 md:w-12 md:h-12 lg:w-8 lg:h-8 bg-green-500 hover:bg-green-600 text-white rounded-full flex items-center justify-center font-bold text-lg sm:text-xl md:text-2xl lg:text-3xl aspect-square p-0">+</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Order Summary -->
        <div class="w-1/3">
          <div class="sticky top-8">
            <div class="bg-white rounded-2xl shadow-xl p-6">

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

                <div class="flex items-center space-x-3 mb-4 text-sm">
                  <input type="checkbox"
                    id="rushOrderCheckbox"
                    name="rushOrder"
                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 pointer-events-none"
                    checked>
                  <label for="rushOrderCheckbox" class="text-lg text-gray-700 font-black cursor-default">Rush Order</label>
                </div>

                <div class="flex justify-between items-center mb-3 text-sm items-header">
                  <span class="text-base font-bold text-gray-700">Items</span>
                  <span class="text-base font-bold text-gray-700">Qty.</span>
                </div>
                <div id="clothes-summary" class="space-y-2 text-sm">
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

            <div class="flex gap-3 mt-6">
              <button type="button" onclick="window.location.href='index.php';"
                class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-lg font-semibold transition">
                Cancel
              </button>
              <button type="submit"
                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-lg font-semibold flex items-center justify-center gap-2 transition">
                <span class="text-xl">+</span>
                <span>Submit</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <input type="hidden" id="tops-qty-input" name="topsQty" value="0">
      <input type="hidden" id="bottoms-qty-input" name="bottomsQty" value="0">
      <input type="hidden" id="undies-qty-input" name="undiesQty" value="0">
      <input type="hidden" id="socks-qty-input" name="socksQty" value="0">
      <input type="hidden" id="towels-qty-input" name="towelsQty" value="0">
      <input type="hidden" id="beds-qty-input" name="bedsQty" value="0">
      <input type="hidden" id="gowns-qty-input" name="gownsQty" value="0">
      <input type="hidden" id="barong-qty-input" name="barongQty" value="0">
      <input type="hidden" id="curtains-qty-input" name="curtainsQty" value="0">
      <input type="hidden" id="comforter-qty-input" name="comforterQty" value="0">

    </form>
  </div>

</main>

</div>

<?php include '../layout/footer.php' ?>