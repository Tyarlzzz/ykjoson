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
          echo '<script>
                  Swal.fire({
                      title: "Error!",
                      text: "Rider not found.",
                      icon: "error"
                  }).then(function() {
                      window.location = "ManageRiders.php";
                  });
              </script>';
          exit();
      }
  } else {
      header('Location: ManageRiders.php');
      exit();
  }

  // Display error message if present
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

<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
  <div class="w-full px-8">
    <div class="mb-6 flex justify-between items-center">
      <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Edit Rider</h1>
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
      <form action="Update.php" method="POST" id="updateRiderForm">
        <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-8">
          <div class="mb-6">
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Full Name</label>
                <input type="text" id="fullname" name="fullname" placeholder="Enter full name" required
                  value="<?= htmlspecialchars($fullname) ?>"
                  class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" placeholder="Enter phone number" required
                  value="<?= htmlspecialchars($phone_number) ?>"
                  pattern="[0-9]{10,11}"
                  title="Please enter 10-11 digit phone number"
                  class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Address</label>
              <input type="text" id="address" name="address" placeholder="Enter address" required
                value="<?= htmlspecialchars($address) ?>"
                class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
            </div>

            <div class="flex items-end mt-6 justify-end gap-3">
              <input type="hidden" name="rider_id" value="<?= htmlspecialchars($rider_id) ?>">

              <button type="button" onclick="window.location.href='ManageRiders.php'"
                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-xl transition">
                <span>Cancel</span>
              </button>

              <button type="submit"
                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-xl flex items-center gap-2 transition">
                <div class="bg-white rounded-full p-1 flex items-center justify-center">
                  <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                  </svg>
                </div>
                <span>Update Rider</span>
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</main>

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

<script src="../assets/js/gas_system_js/gasManageRiders.js"></script>

<?php include '../layout/footer.php'; ?>