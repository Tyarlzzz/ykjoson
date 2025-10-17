<?php 
  require_once '../database/Database.php';
  require_once '../Models/Models.php';
  require_once '../Models/GasOrder.php';

  $database = new Database();
  $conn = $database->getConnection();
  Model::setConnection($conn);

  $orderId = isset($_GET['id']) ? intval($_GET['id']) : null;

  if (!$orderId) {
      header('Location: orderlist.php?error=Invalid order ID');
      exit();
  }

  $orderData = GasOrder::getOrderWithDetails($orderId);

  if (!$orderData) {
      header('Location: orderlist.php?error=Order not found');
      exit();
  }

  $orderItems = GasOrder::getOrderItems($orderId);

  $petronQty = 0;
  $econoQty = 0;
  $seagasQty = 0;

  if ($orderItems) {
      foreach ($orderItems as $item) {
          $brandLower = strtolower($item['item_name']);
          if ($brandLower === 'petron') {
              $petronQty = $item['quantity'];
          } elseif ($brandLower === 'econo') {
              $econoQty = $item['quantity'];
          } elseif ($brandLower === 'seagas') {
              $seagasQty = $item['quantity'];
          }
      }
  }

  require '../layout/header.php';

  if (isset($_GET['error'])) {
      echo '<script>
              Swal.fire({
                  title: "Error!",
                  text: "' . addslashes($_GET['error']) . '",
                  icon: "error"
              });
          </script>';
  }
?>

<!-- Main Content Area -->
<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
  <div class="w-full px-8">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
      <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Edit Order</h1>
      <p class="text-gray-500 text-base"><?php echo date('F j, Y'); ?></p>
    </div>

    <form action="update.php" method="POST" id="orderForm">
      <input type="hidden" name="orderId" value="<?php echo htmlspecialchars($orderData['order_id']); ?>">
      <input type="hidden" name="customerId" value="<?php echo htmlspecialchars($orderData['customer_id']); ?>">

      <div class="flex gap-8">
        <!-- Left Side ng Form -->
        <div class="flex-1">
          <!-- Customer Information -->
          <div class="mb-6">
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Full
                  Name</label>
                <input type="text" id="fullName" name="fullName" placeholder="Enter full name" required
                  value="<?php echo htmlspecialchars($orderData['fullname']); ?>"
                  class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Number</label>
                <input type="text" id="phoneNumber" name="phoneNumber" placeholder="Enter phone number" required
                  value="<?php echo htmlspecialchars($orderData['phone_number']); ?>"
                  pattern="[0-9]{10,11}"
                  title="Please enter 10-11 digit phone number"
                  class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Delivery
                Address</label>
              <input type="text" id="address" name="address" placeholder="Enter delivery address" required
                value="<?php echo htmlspecialchars($orderData['address']); ?>"
                class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Note</label>
              <textarea id="note" name="note" rows="3" placeholder="Add any special notes..."
                class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 resize-none text-gray-800 font-medium"><?php echo htmlspecialchars($orderData['note'] ?? ''); ?></textarea>
            </div>
          </div>

          <!-- Brand Selection -->
          <div class="mb-6">
            <div class="flex items-center">
              <h2 class="text-lg font-medium text-gray-700">Brand Selection</h2>
              <div class="flex-1 h-px bg-black ml-4"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-1">
              <!-- Petron -->
              <div
                class="item-card bg-gray-50 text-center border-2 border-transparent p-3">
                <h3 class="font-bold text-gray-700 text-base sm:text-lg mb-2">Petron</h3>
                <div
                  class="w-full max-w-xs aspect-square mx-auto my-4 p-0 bg-white border-2 border-gray-200 shadow-sm rounded-2xl flex items-center justify-center">
                  <img src="../assets/images/petron.png" alt="Petron" class="w-4/5 h-4/5 object-contain">
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                  <div
                    class="inline-flex border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="petron-qty"
                      class="w-12 text-center font-bold text-gray-700 text-xl"><?php echo $petronQty; ?>
                    </span>
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
                class="item-card bg-gray-50 text-center border-2 border-transparent p-3">
                <h3 class="font-bold text-gray-700 text-base sm:text-lg mb-2">Econo</h3>
                <div
                  class="w-full max-w-xs aspect-square mx-auto my-4 p-0 bg-white border-2 border-gray-200 shadow-sm rounded-2xl flex items-center justify-center">
                  <img src="../assets/images/econo.png" alt="Econo" class="w-4/5 h-4/5 object-contain">
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                  <div
                    class="inline-flex border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="econo-qty"
                      class="w-12 text-center font-bold text-gray-700 text-xl"><?php echo $econoQty; ?>
                    </span>
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
                class="item-card bg-gray-50 text-center border-2 border-transparent p-3">
                <h3 class="font-bold text-gray-700 text-base sm:text-lg mb-2">SeaGas</h3>
                <div
                  class="w-full max-w-xs aspect-square mx-auto my-4 p-0 bg-white border-2 border-gray-200 shadow-sm rounded-2xl flex items-center justify-center">
                  <img src="../assets/images/seagas.png" alt="SeaGas" class="w-4/5 h-4/5 object-contain">
                </div>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-2">
                  <div
                    class="inline-flex border-2 border-gray-200 shadow-sm items-center justify-center bg-white rounded-md px-2 py-1">
                    <span id="seagas-qty"
                      class="w-12 text-center font-bold text-gray-700 text-xl"><?php echo $seagasQty; ?>
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <button type="button" onclick="decreaseQty('seagas')"
                      class="w-10 h-10 bg-red-500 text-white rounded-full flex items-center justify-center font-bold text-xl">−</button>
                    <button type="button" onclick="increaseQty('seagas')"
                      class="w-10 h-10 bg-green-500 text-white rounded-full flex items-center justify-center font-bold text-xl">+</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Side - Order Summary -->
        <div class="w-1/3">
          <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-8">
            <h2 class="border-b border-black text-xl font-bold text-gray-800 mb-6 pb-2">Order Summary</h2>

            <div class="space-y-3 mb-4 text-sm overflow-y-auto max-h-96">
              <div class="flex justify-between items-start">
                <span class="text-gray-600 flex-shrink-0">Name:</span>
                <div class="flex-1 min-w-0 ml-2 break-words text-right">
                  <span id="summary-name" class="font-semibold text-gray-800 block">
                    <?php echo htmlspecialchars($orderData['fullname']); ?>
                  </span>
                </div>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-gray-600 flex-shrink-0">Phone Number:</span>
                <div class="flex-1 min-w-0 ml-2 break-words text-right">
                  <span id="summary-phone" class="font-semibold text-gray-800 block">
                    <?php echo htmlspecialchars($orderData['phone_number']); ?>
                  </span>
                </div>
              </div>
              <div class="flex justify-between items-start">
                <span class="text-gray-600 flex-shrink-0">Address:</span>
                <div class="flex-1 min-w-0 ml-2 break-words text-right">
                  <span id="summary-address" class="font-semibold text-gray-800 block">
                    <?php echo htmlspecialchars($orderData['address']); ?>
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
                <span id="total-items" class="font-bold text-xl text-gray-800"><?php echo ($petronQty + $econoQty + $seagasQty); ?></span>
              </div>
              <div class="text-sm text-gray-600">
                <span class="font-semibold">Notes:</span>
                <p id="summary-notes" class="mt-1 text-gray-500 break-words max-w-full">
                  <?php echo !empty($orderData['note']) ? htmlspecialchars($orderData['note']) : 'No notes'; ?>
                </p>
              </div>
            </div>
          </div>

          <div class="flex gap-3 mt-6">
            <button type="button" onclick="window.location.href='orderlist.php'"
              class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-2xl font-semibold transition">
              Cancel
            </button>
            <button type="submit"
              class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-2xl font-semibold flex items-center justify-center gap-2 transition">
              <span>Update</span>
            </button>
          </div>

          <button type="button" onclick="confirmDeleteOrder(<?php echo $orderId; ?>)"
            class="w-full mt-3 border-2 border-red-500 text-red-500 hover:bg-red-50 py-3 rounded-2xl font-semibold transition">
            Delete Order
          </button>
        </div>
      </div>

      <input type="hidden" id="petron-qty-input" name="petronQty" value="<?php echo $petronQty; ?>">
      <input type="hidden" id="econo-qty-input" name="econoQty" value="<?php echo $econoQty; ?>">
      <input type="hidden" id="seagas-qty-input" name="seagasQty" value="<?php echo $seagasQty; ?>">
      
      <input type="hidden" name="originalPetronQty" value="<?php echo $petronQty; ?>">
      <input type="hidden" name="originalEconoQty" value="<?php echo $econoQty; ?>">
      <input type="hidden" name="originalSeagasQty" value="<?php echo $seagasQty; ?>">
    </form>
  </div>
</main>

</div> 

<script>
  window.initialOrderData = {
    id: <?php echo json_encode($orderData['order_id']); ?>,
    petronQty: <?php echo $petronQty; ?>,
    econoQty: <?php echo $econoQty; ?>,
    seagasQty: <?php echo $seagasQty; ?>
  };

  function confirmDeleteOrder(orderId) {
    Swal.fire({
      title: 'Are you sure?',
      text: "This will permanently delete this order and restore inventory! This action cannot be undone.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Yes, delete it!',
      cancelButtonText: 'Cancel',
      reverseButtons: true,
      focusCancel: true
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Deleting...',
          text: 'Please wait while we delete the order',
          allowOutsideClick: false,
          allowEscapeKey: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        
        window.location.href = `destroy.php?id=${orderId}&confirm=yes`;
      }
    });
  }
</script>

<script src="../assets/js/gas_system_js/gasEditOrder.js"></script>

<?php require '../layout/footer.php' ?>