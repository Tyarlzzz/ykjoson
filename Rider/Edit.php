<?php
  require_once '../layout/header.php';
  require_once '../database/Database.php';
  require_once '../Models/Models.php';
  require_once '../Models/Rider.php';

  $database = new Database();
  $conn = $database->getConnection();
  Model::setConnection($conn);

  $Riders = Rider::getRider();

  $rider_id = null;
  $fullname = '';
  $phone_number = '';
  $address = '';

  if (isset($_GET['id'])) {
      $rider = Rider::find($_GET['id']);
      if ($rider) {
          $rider_id = $rider->rider_id;
          $fullname = $rider->fullname;
          $phone_number = $rider->phone_number;
          $address = $rider->address;
      } else {
          // Handle rider not found, perhaps redirect or show error
          echo '<script>Swal.fire("Error!", "Rider not found.", "error");</script>';
      }
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      try {
          $fullname = $_POST['fullname'];
          $phone_number = $_POST['phone_number'];
          $address = $_POST['address'];
          $rider_id = $_POST['rider_id'];

          $rider = Rider::find($rider_id);
          if (!$rider) {
              throw new Exception('Rider not found');
          }

          $success = $rider->update([
              'fullname' => $fullname,
              'phone_number' => $phone_number,
              'address' => $address
          ]);

          if ($success) {
              echo '<script>
                      Swal.fire({
                          title: "Success!",
                          text: "Rider updated successfully!",
                          icon: "success"
                      }).then(function() {
                          window.location = "ManageRiders.php";
                      });
                  </script>';
          } else {
              throw new Exception('Failed to update rider');
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
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold rounded-t-2xl z-0">Inventory</a>
      <a href="salesReport.php"
        class="px-5 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Sales
        Report</a>
      <a href="manageRiders.php"
        class="px-10 py-1 bg-red-600 text-white font-semibold rounded-t-2xl -ml-3 z-0">Riders</a>
      <a href="archived.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Archived</a>
      <a href="expenses.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Expenses</a>
    </div>

    <!-- Container -->
    <div class="w-full bg-white rounded-lg rounded-tl-none shadow-md border border-gray-200 overflow-hidden">
      <form action="Edit.php" method="POST" id="orderForm">
        <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-8">
          <div class="mb-6">
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Full
                  Name</label>
                <input type="text" id="fullname" name="fullname" placeholder="Enter full name"
                  value="<?= htmlspecialchars($fullname) ?>"
                  class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Number</label>
                <input type="text" id="phone_number" name="phone_number" placeholder="Enter phone number"
                  value="<?= htmlspecialchars($phone_number) ?>"
                  class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">
                Address</label>
              <input type="text" id="address" name="address" placeholder="Enter address"
                value="<?= htmlspecialchars($address) ?>"
                class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
            </div>

            <div class="flex items-end mt-6 justify-end gap-3">
              <?php if (isset($rider_id)): ?>
                <input type="hidden" name="rider_id" value="<?= htmlspecialchars($rider_id) ?>">
              <?php endif; ?>

              <button type="button" onclick="window.location.href='manageRiders.php'"
                class="bg-gray-500 text-white p-3 rounded-xl transition">
                <span>Cancel</span>
              </button>

              <button type="submit"
                class="bg-blue-500 text-white p-3 rounded-xl flex items-center gap-2">
                <div class="bg-white rounded-full p-1 flex items-center justify-center">
                  <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                  </svg>
                </div>
                <span>Update Rider</span>
              </button>
            </div>
          </div>

          <div class="flex items-center">
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
                      <a
                        href="Edit.php?id=<?= $rider->rider_id ?>&name=<?= urlencode($rider->fullname) ?>&phone=<?= urlencode($rider->phone_number) ?>&address=<?= urlencode($rider->address) ?>">
                        <button type="button"
                          class="bg-yellow-400 text-white px-3 py-1 rounded-lg transition">
                          Edit
                        </button>
                      </a>
                      <button type="button" class="bg-red-500 text-white px-3 py-1 rounded-lg transition delete-btn" data-id="<?= $rider->rider_id ?>">
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
      </form>
    </div>
  </div>
</main>

<script src="../assets/js/gas_system_js/gasManageRiders.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-btn');

    deleteButtons.forEach(button => {
      button.addEventListener('click', function() {
        const rider_id = this.dataset.id;

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
            window.location.href = `Destroy.php?id=${rider_id}`;
          }
        });
      });
    });
  });
</script>

<?php include '../layout/footer.php'; ?>