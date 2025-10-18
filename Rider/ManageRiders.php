<?php
require_once '../layout/header.php';
require_once '../database/Database.php';
require_once '../Models/Models.php';
require_once '../Models/Rider.php';

$database = new Database();
$conn = $database->getConnection();
Model::setConnection($conn);

$Riders = Rider::all();

$previousURL = $_SERVER['HTTP_REFERER'] ?? null;

if (strpos($previousURL, 'Laundry') !== false) { 
  $systemBase = '../Laundry/'; 
} else { 
  $systemBase = '../Gas/'; 
}

if (isset($_GET['success'])) {
  echo '<script>
                Swal.fire({
                    title: "Success!",
                    text: "You have successfully updated the rider.",
                    icon: "success"
                });
            </script>';
}

if (isset($_GET['error'])) {
  echo '<script>
                Swal.fire({
                    title: "Error!",
                    text: "Failed to update the rider.",
                    icon: "error"
                });
            </script>';
}
?>

<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
  <div class="w-full">
    <div class="mb-6 flex justify-between items-center">
      <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Inventory & Sales Report</h1>
      <p class="text-gray-500 text-base"><?php echo date('F j, Y'); ?></p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex mb-0">
        <a href="<?php echo $systemBase; ?>expenses.php"
          class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl">Expenses</a>
        <a href="<?php echo $systemBase; ?>salesReport.php"
          class="px-5 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Sales
          Report</a>
        <?php if (strpos($previousURL, 'Laundry') !== false) { ?>
          <a href="<?php echo $systemBase; ?>pricing.php"
          class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Pricing</a>
        <?php } ?>
          <?php if (strpos($previousURL, 'Gas') !== false) { ?>
          <a href="<?php echo $systemBase; ?>updateInventory.php"
            class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Inventory</a>
        <?php } ?>
        <a href="manageRiders.php"
          class="px-10 py-1<?php if (strpos($previousURL, 'Gas') !== false) { ?> bg-red-600<?php } else { ?> bg-blue-600<?php } ?> text-white font-base rounded-t-2xl -ml-3 z-0">Riders</a>
        <a href="<?php echo $systemBase; ?>archived.php"
          class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Archived</a>
    </div>

    <!-- Container -->
    <div class="w-full bg-white rounded-lg rounded-tl-none shadow-md border border-gray-200 overflow-hidden">
      <form action="store.php" method="POST" id="orderForm">
        <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-8">
          <div class="mb-6">
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Full Name</label>
                <input type="text" id="fullname" name="fullname" placeholder="Enter full name" required
                  class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" placeholder="Enter phone number" required
                  pattern="[0-9]{10,11}" title="Please enter 10-11 digit phone number"
                  class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Address</label>
              <input type="text" id="address" name="address" placeholder="Enter address" required
                class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
            </div>

            <div class="flex item-end mt-6">
              <button type="submit"
                class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-lg font-semibold flex items-center justify-center gap-2 transition">
                <span class="text-xl">+</span>
                <span>Add Rider</span>
              </button>
            </div>
          </div>

          <div class="flex items-center mt-6">
            <h2 class="text-lg font-bold text-gray-700">Rider Lists</h2>
            <div class="flex-1 h-px bg-black ml-4"></div>
          </div>

          <div class="mt-4 max-h-64 overflow-y-auto overflow-x-auto">
            <table class="w-full table-auto border-collapse">
              <thead>
                <tr class="bg-gray-200">
                  <th class="px-4 py-2 border-b border-gray-300 text-left">#</th>
                  <th class="px-4 py-2 border-b border-gray-300 text-left">Name</th>
                  <th class="px-4 py-2 border-b border-gray-300 text-left">Phone Number</th>
                  <th class="px-4 py-2 border-b border-gray-300 text-left">Address</th>
                  <th class="px-4 py-2 border-b border-gray-300 text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $counter = 1;
                if (!empty($Riders)):
                  ?>
                  <?php foreach ($Riders as $rider): ?>
                    <tr class="hover:bg-gray-100">
                      <td class="px-4 py-2 border-b border-gray-300">
                        <?= htmlspecialchars($counter++) ?>
                      </td>
                      <td class="px-4 py-2 border-b border-gray-300">
                        <?= htmlspecialchars($rider->fullname) ?>
                      </td>
                      <td class="px-4 py-2 border-b border-gray-300">
                        <?= htmlspecialchars($rider->phone_number) ?>
                      </td>
                      <td
                        class="px-4 py-2 border-b border-gray-300 truncate overflow-hidden text-ellipsis whitespace-nowrap max-w-[200px]">
                        <?= htmlspecialchars($rider->address) ?>
                      </td>
                      <td class="px-4 py-2 border-b border-gray-300 text-center">
                        <a href="Edit.php?id=<?= $rider->rider_id ?>">
                          <button type="button"
                            class="bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded-lg font-semibold transition">
                            Edit
                          </button>
                        </a>
                        <button type="button"
                          class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-lg font-semibold transition delete-btn"
                          data-id="<?= $rider->rider_id ?>">
                          Delete
                        </button>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="5" class="text-center py-4 text-gray-500">
                      No riders found.
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </form>
    </div>
  </div>
</main>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const deleteButtons = document.querySelectorAll('.delete-btn');

    deleteButtons.forEach(button => {
      button.addEventListener('click', function () {
        const riderId = this.dataset.id;

        Swal.fire({
          title: 'Are you sure?',
          text: "This action cannot be undone.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = `Destroy.php?id=${riderId}`;
          }
        });
      });
    });
  });
</script>

<script src="../assets/js/gas_system_js/gasManageRiders.js"></script>

<?php include '../layout/footer.php'; ?>