<?php
  
require_once '../db\db_connect.php';
require_once '../models/User.php';
require_once '../models/Customer.php';
require_once '../models/Order.php';
require_once '../models/Product_code.php';
require_once '../models/Transaction.php';
require_once '../models/Item_allotment.php';
require_once '../models/Item_inventory.php';
require_once '../models/Assigned_Delivery.php';
require_once '../models/Ordered_item.php';

include '../layout/header.php';

    $db = new Database();
    $conn = $db->getConnection();
    Order::setConnection($conn);
    Customer::setConnection($conn); 
    User::setConnection($conn);
    Product_code::setConnection($conn);

    $products = Product_code::all();
?>

<?php
    require_once '../../database/Database.php';
    require_once '../../models/Course.php'; 

    $db = new Database();
    $conn = $db->getConnection();
    Course::setConnection($conn); 

    include '../../layout/header.php'; 
        
?>

<div>
    <div>
        <div>
            <form action="../orders/store.php" method="POST">
                <h1>ADD COURSE</h1>
                <div>
                    <div>
                        <label for="first_name" >First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                </div>
                <div>
                    <div>
                        <label for="last_name" >Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>
                <div>
                    <div>
                        <label for="phone_number" >Customer Number</label>
                        <input type="text" id="phone_number" name="phone_number" required>
                    </div>
                </div>
                <div>
                    <div>
                        <label for="address">Address</label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                </div>
                  <h1> Categories</h1>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Course ID</th>
                                    <th>Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $i = 1;
                                    foreach($courses as $course):
                                ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= $course->code ?></td>
                                        <td><?= $course->name ?></td>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table> 
                          <h1 class="text-center fw-bolder mb-4"><i class="fa-solid fa-book-open-reader"></i> Courses</h1>
                        <table id="coursesTable" class="table table-striped table-hover table-bordered table-responsive">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Course ID</th>
                                    <th>Name</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $i = 1;
                                    foreach($courses as $course):
                                ?>
                                    <tr>
                                        <td><?= $i++ ?></td>
                                        <td><?= $course->code ?></td>
                                        <td><?= $course->name ?></td>
                                        <td>
                                            <!--View--> 
                                            <a href="view.php?id=<?=$course->id?>" class="btn btn-success">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
                                                    <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                                                    <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                                                </svg>
                                            </a>
                                            <!--Edit--> 
                                            <a href="edit.php?id=<?=$course->id?>" class="btn btn-primary">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16">
                                                    <path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/>
                                                    <path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5z"/>
                                                </svg>
                                        </a>
                                        <!--Delete if role is admin/superadmin--> 
                                          <!--  <?// if(($_SESSION['role'] == 'admin')||($_SESSION['role'] == 'superAdmin')){?> -->
                                                <a href="destroy.php?id=<?=$course->id?>" class="btn btn-danger">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                                        <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
                                                    </svg>
                                                </a>
                                           <!-- <}?> -->
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>#</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table> 
                    <div>
                        <button type="submit">Add</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
    

include '../../layout/footer.php'; ?>


    
    

