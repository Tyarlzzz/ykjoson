        </div>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/jquery.dataTables.min.js"></script>
    
    <?php
    // Get the current directory to determine which system is being used
    $currentDir = basename(dirname($_SERVER['PHP_SELF']));
    
    if ($currentDir === 'Gas') {
        // Load Gas system datatables
        echo '<script src="../assets/js/gas_system_js/gasDatatables.js"></script>';
    } else {
        // Load Laundry system datatables (default to)
        echo '<script src="../assets/js/datatables.js"></script>';
    }
    ?>
    
</body>
</html>