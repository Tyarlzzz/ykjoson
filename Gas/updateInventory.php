<?php include '../layout/header.php'; ?>

<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
  <div class="w-full px-8">
    <div class="mb-6 flex justify-between items-center">
      <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Inventory & Sales Report</h1>
      <p class="text-gray-500 text-base"><?php echo date('F j, Y'); ?></p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex mb-0">
      <a href="updateInventory.php"
        class="px-8 py-1 bg-red-600 text-white font-semibold rounded-t-2xl z-0">Inventory</a>
      <a href="inventorySales.php"
        class="px-5 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Sales
        Report</a>
      <a href="ManageRiders.php"
        class="px-10 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Riders</a>
      <a href="archived.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Archived</a>
    </div>

    <!-- Container -->
    <div class="w-full bg-white rounded-lg rounded-tl-none shadow-md border border-gray-200 overflow-hidden">
      <!-- Gas Cards -->
      <div class="p-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

          <!-- Petron Card -->
          <div class="gas-card bg-white border-2 border-gray-200 rounded-2xl p-6 cursor-pointer" data-brand="petron"
            data-stock="55" data-price="1069">
            <div class="flex flex-col items-center">
              <img src="../assets/images/petron.png" alt="Petron" class="w-3/5 h-3/5 mb-4">
              <h3 class="text-xl font-bold text-gray-800 mb-2">Petron</h3>
              <div class="text-center space-y-1">
                <p class="text-gray-600">Stock: <span class="font-semibold text-gray-800" id="petron-stock">55</span>
                </p>
                <p class="text-gray-600">Price: <span class="font-semibold text-gray-800">₱<span
                      id="petron-price">1069</span></span></p>
              </div>
            </div>
          </div>

          <!-- Econo Card -->
          <div class="gas-card bg-white border-2 border-gray-200 rounded-2xl p-6 cursor-pointer " data-brand="econo"
            data-stock="14" data-price="1020">
            <div class="flex flex-col items-center">
              <img src="../assets/images/econo.png" alt="Econo" class="w-3/5 h-3/5 mb-4">
              <h3 class="text-xl font-bold text-gray-800 mb-2">Econo</h3>
              <div class="text-center space-y-1">
                <p class="text-gray-600">Stock: <span class="font-semibold text-gray-800" id="econo-stock">14</span></p>
                <p class="text-gray-600">Price: <span class="font-semibold text-gray-800">₱<span
                      id="econo-price">1020</span></span></p>
              </div>

              <!-- Ex. Low Stock Warning -->
              <div
                class="mt-3 bg-yellow-100 border border-yellow-400 text-yellow-700 px-3 py-1 rounded-full text-sm flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd"></path>
                </svg>
                Stock is currently low
              </div>
            </div>
          </div>

          <!-- SeaGas Card -->
          <div class="gas-card bg-white border-2 border-gray-200 rounded-2xl p-6 cursor-pointer " data-brand="seagas"
            data-stock="60" data-price="1000">
            <div class="flex flex-col items-center">
              <img src="../assets/images/seagas.png" alt="SeaGas" class="w-3/5 h-3/5 mb-4">
              <h3 class="text-xl font-bold text-gray-800 mb-2">SeaGas</h3>
              <div class="text-center space-y-1">
                <p class="text-gray-600">Stock: <span class="font-semibold text-gray-800" id="seagas-stock">60</span>
                </p>
                <p class="text-gray-600">Price: <span class="font-semibold text-gray-800">₱<span
                      id="seagas-price">1000</span></span></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Select Brand -->
        <div class="text-center text-gray-600 text-lg" id="chooseText">
          Choose brand inventory to update
        </div>

        <!-- Update Form (Hidden when no brand is tapped) -->
        <div class="mt-8 hidden" id="updateForm">
          <div class="border border-gray-200 rounded-2xl p-2">
            <h2 class="text-2xl font-bold text-gray-800 mx-3 mt-2">Update Inventory</h2>

            <!-- Brand Name -->
            <div class="flex items-center my-4">
              <img id="updateBrandImage" src="" alt="" class="w-10 h-10 ml-1">
              <h2 class="text-xl font-bold text-gray-800" id="updateBrandName"></h2>
              <div class="flex-1 h-px bg-black mx-4"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mx-3 mb-4">

              <!-- Stock Input -->
              <div>
                <label for="stockInput"
                  class="block text-sm font-medium text-gray-700 uppercase tracking-wider mb-1">Stock</label>
                <input type="number" id="stockInput"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors text-lg"
                  placeholder="Enter stock quantity" min="0">
              </div>

              <!-- Price Input -->
              <div>
                <label for="priceInput"
                  class="block text-sm font-medium text-gray-700 uppercase tracking-wider mb-1">Price</label>
                <input type="number" id="priceInput"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors text-lg"
                  placeholder="Enter price" min="0" step="0.01">
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 mx-3 mb-3">
              <button id="cancelBtn" class="px-8 py-3 bg-gray-500 text-white font-semibold rounded-xl">
                Cancel
              </button>
              <button id="confirmBtn" class="px-8 py-3 bg-red-600 text-white font-semibold rounded-xl">
                Confirm
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>

  // Gas card selection and update functionality
  const gasCards = document.querySelectorAll('.gas-card');
  const updateForm = document.getElementById('updateForm');
  const chooseText = document.getElementById('chooseText');
  const updateBrandImage = document.getElementById('updateBrandImage');
  const updateBrandName = document.getElementById('updateBrandName');
  const stockInput = document.getElementById('stockInput');
  const priceInput = document.getElementById('priceInput');
  const cancelBtn = document.getElementById('cancelBtn');
  const confirmBtn = document.getElementById('confirmBtn');

  let selectedCard = null;
  let selectedBrand = null;

  // Brand images
  const brandImages = {
    petron: '../../assets/images/petron.png',
    econo: '../../assets/images/econo.png',
    seagas: '../../assets/images/seagas.png'
  };

  // Brand names
  const brandNames = {
    petron: 'Petron',
    econo: 'Econo',
    seagas: 'SeaGas'
  };

  // Card selection functionality
  gasCards.forEach(card => {
    card.addEventListener('click', function () {
      const brand = this.dataset.brand;
      const currentStock = this.dataset.stock;
      const currentPrice = this.dataset.price;

      // Reset all cards
      gasCards.forEach(c => {
        c.classList.remove('bg-red-100', 'border-red-300');
        c.classList.add('border-gray-200');
      });

      // Highlight selected card
      this.classList.add('bg-red-100', 'border-red-300');
      this.classList.remove('border-gray-200');

      // Update form
      selectedCard = this;
      selectedBrand = brand;
      updateBrandImage.src = brandImages[brand];
      updateBrandImage.alt = brandNames[brand];
      updateBrandName.textContent = brandNames[brand];
      stockInput.value = currentStock;
      priceInput.value = currentPrice;

      // Show form and hide instruction text
      chooseText.classList.add('hidden');
      updateForm.classList.remove('hidden');
    });
  });

  // Cancel button
  cancelBtn.addEventListener('click', function () {
    // reset selection effects
    gasCards.forEach(card => {
      card.classList.remove('bg-red-100', 'border-red-300');
      card.classList.add('border-gray-200');
    });

    // hide update form and text
    updateForm.classList.add('hidden');
    chooseText.classList.remove('hidden');

    // reset variables
    selectedCard = null;
    selectedBrand = null;
  });

  // Confirm button functionality
  confirmBtn.addEventListener('click', function () {
    if (!selectedCard || !selectedBrand) return;

    // Update stock and price in the card
    const newStock = parseInt(stockInput.value);
    const newPrice = parseFloat(priceInput.value).toFixed(2);

    selectedCard.dataset.stock = newStock;
    selectedCard.dataset.price = newPrice;

    // Update displayed values
    document.getElementById(`${selectedBrand}-stock`).textContent = newStock;
    document.getElementById(`${selectedBrand}-price`).textContent = newPrice;

    // reset the form
    cancelBtn.click();

  });

  // Input validation and formatting
  priceInput.addEventListener('input', function () {
    let value = this.value;
    if (value.includes('.')) {
      const parts = value.split('.');
      if (parts[1] && parts[1].length > 2) {
        this.value = parts[0] + '.' + parts[1].substring(0, 2);
      }
    }
  });

  stockInput.addEventListener('input', function () {
    if (this.value < 0) {
      this.value = 0;
    }
  });

  // Form validation feedback
  function addInputValidation() {
    [stockInput, priceInput].forEach(input => {
      input.addEventListener('blur', function () {
        if (this.value === '' || (this.type === 'number' && this.value < 0)) {
          this.classList.add('border-red-500');
          this.classList.remove('border-gray-300');
        } else {
          this.classList.remove('border-red-500');
          this.classList.add('border-gray-300');
        }
      });

      input.addEventListener('focus', function () {
        this.classList.remove('border-red-500');
        this.classList.add('border-red-500');
      });
    });
  }

  // Initialize input validation
  addInputValidation();
</script>

<?php include '../layout/footer.php'; ?>