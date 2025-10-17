<?php
  require_once '../layout/header.php';
  require_once '../database/Database.php';
  require_once '../Models/Models.php';
  require_once '../Models/Item_inventory.php';

  $database = new Database();
  $conn = $database->getConnection();
  Model::setConnection($conn);

  // Get all gas inventory
  $gasInventory = Item_inventory::getGasInventory();

  // Create associative array for easy access
  $inventory = [];
  if ($gasInventory) {
      foreach ($gasInventory as $item) {
          $inventory[strtolower($item->item_name)] = $item;
      }
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      try {
          $brand = $_POST['brand'];
          $stock = $_POST['stock'];
          $price = $_POST['price'];

          if (empty($brand) || $stock < 0 || $price < 0) {
              throw new Exception('Invalid input data');
          }

          $item = Item_inventory::getByItemName($brand);

          if (!$item) {
              throw new Exception('Item not found');
          }

          $success = Item_inventory::updateStockAndPrice($item->item_id, $stock, $price);

          if ($success) {
              echo '<script>
                      Swal.fire({
                          title: "Success!",
                          text: "' . ucfirst($brand) . ' inventory updated successfully!",
                          icon: "success"
                      }).then(function() {
                          window.location = "updateInventory.php";
                      });
                  </script>';
          } else {
              throw new Exception('Failed to update inventory');
          }

      } catch (Exception $e) {
          echo '<script>
                  Swal.fire({
                      title: "Error!",
                      text: "' . addslashes($e->getMessage()) . '",
                      icon: "error"
                  });
              </script>';
      }
  }
?>

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
      <a href="salesReport.php"
        class="px-5 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Sales
        Report</a>
      <a href="../Rider/manageRiders.php"
        class="px-10 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Riders</a>
      <a href="archived.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Archived</a>
      <a href="expenses.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Expenses</a>
    </div>

    <!-- Container -->
    <div class="w-full bg-white rounded-lg rounded-tl-none shadow-md border border-gray-200 overflow-hidden">
      <!-- Gas Cards -->
      <div class="p-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

          <!-- Petron Card -->
          <div class="gas-card bg-white border-2 border-gray-200 rounded-2xl p-6 cursor-pointer" 
              data-brand="petron"
              data-stock="<?php echo $inventory['petron']->stocks; ?>" 
              data-price="<?php echo $inventory['petron']->cost; ?>">
            <div class="flex flex-col items-center">
              <img src="../assets/images/petron.png" alt="Petron" class="w-3/5 h-3/5 mb-4">
              <h3 class="text-xl font-bold text-gray-800 mb-2">Petron</h3>
              <div class="text-center space-y-1">
                <p class="text-gray-600">Stock: <span class="font-semibold text-gray-800" id="petron-stock">
                  <?php echo $inventory['petron']->stocks; ?>
                </span></p>
                <p class="text-gray-600">Price: <span class="font-semibold text-gray-800">₱<span id="petron-price">
                  <?php echo isset($inventory['petron']) ? number_format($inventory['petron']->cost, 2) : '0.00'; ?>
                </span></span></p>
              </div>

              <!-- Low Stock Warning -->
              <div class="low-stock-warning <?php echo (isset($inventory['petron']) && $inventory['petron']->isLowStock()) ? '' : 'hidden'; ?> mt-3 bg-yellow-100 border border-yellow-400 text-yellow-700 px-3 py-1 rounded-full text-sm flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                Stock is currently low
              </div>
            </div>
          </div>

          <!-- Econo Card -->
          <div class="gas-card bg-white border-2 border-gray-200 rounded-2xl p-6 cursor-pointer" 
              data-brand="econo"
              data-stock="<?php echo $inventory['econo']->stocks; ?>" 
              data-price="<?php echo $inventory['econo']->cost; ?>">
            <div class="flex flex-col items-center">
              <img src="../assets/images/econo.png" alt="Econo" class="w-3/5 h-3/5 mb-4">
              <h3 class="text-xl font-bold text-gray-800 mb-2">Econo</h3>
              <div class="text-center space-y-1">
                <p class="text-gray-600">Stock: <span class="font-semibold text-gray-800" id="econo-stock">
                  <?php echo $inventory['econo']->stocks; ?>
                </span></p>
                <p class="text-gray-600">Price: <span class="font-semibold text-gray-800">₱<span id="econo-price">
                  <?php echo isset($inventory['econo']) ? number_format($inventory['econo']->cost, 2) : '0.00'; ?>
                </span></span></p>
              </div>

              <!-- Low Stock Warning -->
              <div class="low-stock-warning <?php echo (isset($inventory['econo']) && $inventory['econo']->isLowStock()) ? '' : 'hidden'; ?> mt-3 bg-yellow-100 border border-yellow-400 text-yellow-700 px-3 py-1 rounded-full text-sm flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                Stock is currently low
              </div>
            </div>
          </div>

          <!-- SeaGas Card -->
          <div class="gas-card bg-white border-2 border-gray-200 rounded-2xl p-6 cursor-pointer" 
              data-brand="seagas"
              data-stock="<?php echo $inventory['seagas']->stocks; ?>" 
              data-price="<?php echo $inventory['seagas']->cost; ?>">
            <div class="flex flex-col items-center">
              <img src="../assets/images/seagas.png" alt="SeaGas" class="w-3/5 h-3/5 mb-4">
              <h3 class="text-xl font-bold text-gray-800 mb-2">SeaGas</h3>
              <div class="text-center space-y-1">
                <p class="text-gray-600">Stock: <span class="font-semibold text-gray-800" id="seagas-stock">
                  <?php echo $inventory['seagas']->stocks; ?>
                </span></p>
                <p class="text-gray-600">Price: <span class="font-semibold text-gray-800">₱<span id="seagas-price">
                  <?php echo isset($inventory['seagas']) ? number_format($inventory['seagas']->cost, 2) : '0.00'; ?>
                </span></span></p>
              </div>

              <!-- Low Stock Warning -->
              <div class="low-stock-warning <?php echo (isset($inventory['seagas']) && $inventory['seagas']->isLowStock()) ? '' : 'hidden'; ?> mt-3 bg-yellow-100 border border-yellow-400 text-yellow-700 px-3 py-1 rounded-full text-sm flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
                Stock is currently low
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
          <form method="POST" action="" class="border border-gray-200 rounded-2xl p-2">
            <h2 class="text-2xl font-bold text-gray-800 mx-3 mt-2">Update Inventory</h2>

            <!-- Brand Name -->
            <div class="flex items-center my-4">
              <img id="updateBrandImage" src="" alt="" class="w-10 h-10 ml-1">
              <h2 class="text-xl font-bold text-gray-800" id="updateBrandName"></h2>
              <div class="flex-1 h-px bg-black mx-4"></div>
            </div>

            <!-- Hidden field for brand identifier -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mx-3 mb-4">
              <input type="hidden" id="brandInput" name="brand" value="">

              <!-- Stock Input -->
              <div>
                <label for="stockInput"
                  class="block text-sm font-medium text-gray-700 uppercase tracking-wider mb-1">Stock</label>
                <input type="number" id="stockInput" name="stock"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors text-lg"
                  placeholder="Enter stock quantity" min="0" required>
              </div>

              <!-- Price Input -->
              <div>
                <label for="priceInput"
                  class="block text-sm font-medium text-gray-700 uppercase tracking-wider mb-1">Price</label>
                <input type="number" id="priceInput" name="price"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors text-lg"
                  placeholder="Enter price" min="0" step="0.01" required>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 mx-3 mb-3">
              <button type="button" id="cancelBtn" class="px-8 py-3 bg-gray-500 text-white font-semibold rounded-xl">
                Cancel
              </button>
              <button type="submit" class="px-8 py-3 bg-red-600 text-white font-semibold rounded-xl">
                Confirm
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</main>

<script src="../assets/js/gas_system_js/updateInventory.js"></script>
<?php include '../layout/footer.php'; ?>