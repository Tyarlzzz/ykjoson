<?php
    require '../layout/header.php';
    require_once '../database/Database.php';
    require_once '../Models/Models.php';
    require_once '../Models/Order.php';
    require_once '../Models/GasCustomer.php';
    require_once '../Models/GasOrder.php';

    $database = new Database();
    $conn = $database->getConnection();
    Model::setConnection($conn);

    $gasOrders = GasOrder::getAllOrdersWithDetails();
?>

<main class="flex-1 overflow-x-hidden overflow-y-hidden h-screen flex flex-col">
    <div class="justify-between flex">
        <button class="bg-[#41D72A] font-[Outfit] text-white font-bold text-xl rounded-xl shadow-lg p-4 ms-6 mt-5">Change Status</button>
        <a href="">
            <div class="bg-gradient-to-br from-red-600 via-red-500 to-red-300 to-white-500 to-white-400 rounded-2xl shadow-xl mt-5 me-6">
                <div class="flex gap-3 p-3">
                    <div class="w-10 h-10 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="-5.0 -10.0 110.0 135.0">
                            <path d="m83.602 16.398c-18.5-18.5-48.699-18.5-67.199 0s-18.5 48.699 0 67.199 48.699 18.5 67.199 0c18.5-18.496 18.5-48.699 0-67.199zm-9.1016 37.801h-20.398v20.398h-8.3984v-20.398h-20.301v-8.3984h20.301l-0.003906-20.301h8.3984v20.301h20.301z" fill="white"/>
                        </svg>
                    </div>
                    <span class="font-[Switzer] text-white font-bold text-2xl mt-1">Add Order</span>
                </div>
            </div>
        </a>
    </div>
    <div class="p-6 bg-white rounded-xl shadow-xl mx-6 mt-6 mb-4 flex-1 flex flex-col min-h-0">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-[Outfit] space-x-2">Today's Orders&nbsp;&nbsp;<span class="font-[Switzer] text-sm"><?php echo date("F j, Y");?></span></h2>
            <div class="flex items-center space-x-3">
                <div class="bg-gray-200 rounded-lg">
                    <button class="ps-7 pe-7 p-3 select-btn">
                        Select all
                    </button>
                </div>
                <div class="relative ms-2">
                    <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" 
                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1110.5 3a7.5 7.5 0 016.15 13.65z"/>
                    </svg>
                    <input id="customSearch" type="text" placeholder="Search..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 w-64"/>
                </div>
                <select id="statusFilter" class="border border-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">All Status</option>
                    <option value="Delivered">Delivered</option>
                    <option value="Pending">Pending</option>
                    <option value="Borrowed">Borrowed</option>
                    <option value="Returned">Returned</option>
                </select>
                <div>
                    <a href="dashboard.php" class="duration-100"> <!-- eto ung maximize button kasi clickable toh-->
                        <svg class="w-6 h-6 rotate-180" xmlns="http://www.w3.org/2000/svg" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 80" fill="none" x="0px" y="0px">
                            <path d="M40.2196 2C39.115 2 38.2196 2.89543 38.2196 4C38.2196 5.10457 39.115 6 40.2196 6H55.1716L27.5858 33.5858C26.8047 34.3668 26.8047 35.6332 27.5858 36.4142C28.3668 37.1953 29.6332 37.1953 30.4142 36.4142L58 8.82843V24C58 25.1046 58.8954 26 60 26C61.1046 26 62 25.1046 62 24V4C62 2.89543 61.1046 2 60 2H40.2196Z" fill="black"/>
                            <path d="M52 37C52 35.8954 51.1046 35 50 35C48.8954 35 48 35.8954 48 37V56C48 57.1046 47.1046 58 46 58H8C6.89543 58 6 57.1046 6 56L6 18C6 16.8954 6.89543 16 8 16L27 16C28.1046 16 29 15.1046 29 14C29 12.8954 28.1046 12 27 12L8 12C4.68629 12 2 14.6863 2 18L2 56C2 59.3137 4.68629 62 8 62H46C49.3137 62 52 59.3137 52 56V37Z" fill="black"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto overflow-y-auto flex-1">
        <table id="orderlistTable" class="w-full">
            <thead>
                <!-- SAMPLE DATA LANG ITONG MGA NILAGAY KO DAPAT NAKA FOR EACH NA YAN -->
                <tr>
                    <th></th>
                    <th>#</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Phone Number</th>
                    <th>Qty</th>
                    <th>Status</th> <!--NAKA AUTO CHANGE COLOR NARIN TOH KAYA WALA NA KAYO PROBLEMA -->
                </tr>
            </thead>
            <tbody>
                <?php if ($gasOrders && count($gasOrders) > 0): ?>
                                <?php $counter = 1; ?>
                                <?php foreach ($gasOrders as $order): ?>
                                    <tr>
                                        <td><input type="checkbox" class="appearance-none peer rounded-md border-1 checked:bg-green-500 checked:border-green-500 w-6 h-6 mt-2"></td>
                                        <td><?php echo $counter++; ?></td> <!-- dito diba mag kulay red ang id kapag rushed order. Ang naisip ko is if customer.rushed == true, mag red ang id -->
                                        <td><a href="edit.php?id=1"><?php echo $order['fullname']; ?></a></td><!-- dito mo ilalagay ung link para maedit ung order nung customer -->
                                        <td><a href="edit.php?id=1"><?php echo $order['address']; ?></a></td>
                                        <td><a href="edit.php?id=1"><?php echo $order['phone_number']; ?></a></td>
                                        <td><?php echo $order['total_quantity']; ?></td> <!-- etong quantity nga pala lagyan niyo condition na kapag ung customer ay rushed mag kakaroon ng box
                                                    ung pinaka qty nya na kulay red tapos ung text ng number ay kulay white (reference: Figma Prototype) -->
                                        <td><?php echo $order['status']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                      <td colspan="8" class="text-center py-8 text-gray-500 italic">
                          No gasOrders Orders Found.
                      </td>
                  </tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</main>

<?php include '../layout/footer.php' ?>