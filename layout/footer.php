        </div>
<script>

document.addEventListener('DOMContentLoaded', function() {

  const sidebar = document.getElementById('sidebar');
  const collapseBtn = document.getElementById('collapseBtn');
  const collapseIcon = document.getElementById('collapseIcon');
  const switchSystemBtn = document.querySelector('.switchSystem');
  const switchSystemText = document.getElementById('switchSystemText');
  
  const dashboardLink = document.getElementById('dashboardLink');
  const addOrderLink = document.getElementById('addOrderLink');
  const orderListLink = document.getElementById('orderListLink');
  const pettyCashLink = document.getElementById('pettyCashLink');
  const inventoryLink = document.getElementById('inventorySales');

  const navLinks = {
    laundry: {
      dashboard: '../Laundry/index.php',
      addOrder: '../Laundry/create.php',
      orderList: '../Laundry/orderlist.php',
      pettyCash: '../Rider/PettyCash.php',
      inventory: '../Laundry/expenses.php'
    },
    gas: {
      dashboard: '../Gas/index.php',
      addOrder: '../Gas/create.php',
      orderList: '../Gas/OrderList.php',
      pettyCash: '../Rider/PettyCash.php',
      inventory: '../Gas/expenses.php'
    }
  };

  let currentSystem = localStorage.getItem('currentSystem') || 'laundry';

  function updateSystemState(system) {
    if (system === 'gas') {
      sidebar.classList.add('gas-mode');
      switchSystemText.textContent = 'Laundry System';
      document.title = 'PoS Gas';
    } else {
      sidebar.classList.remove('gas-mode');
      switchSystemText.textContent = 'Gas System';
      document.title = 'PoS Laundry';
    }
    updateLinks(system);
  }

  function updateLinks(system) {
    const links = navLinks[system];
    dashboardLink.href = links.dashboard;
    addOrderLink.href = links.addOrder;
    orderListLink.href = links.orderList;
    pettyCashLink.href = links.pettyCash;
    inventoryLink.href = links.inventory;
  }

  updateSystemState(currentSystem);

  switchSystemBtn.addEventListener('click', (e) => {
    e.preventDefault();
    
    currentSystem = currentSystem === 'laundry' ? 'gas' : 'laundry';
    localStorage.setItem('currentSystem', currentSystem);
    
    window.location.href = navLinks[currentSystem].dashboard;
  });

  function setCollapsed(collapsed) {
    if (collapsed) {
      sidebar.classList.add('collapsed');
      collapseIcon.classList.add('rotate-180');
      collapseBtn.setAttribute('aria-expanded', 'false');
      localStorage.setItem('sidebarCollapsed', 'true');
    } else {
      sidebar.classList.remove('collapsed');
      collapseIcon.classList.remove('rotate-180');
      collapseBtn.setAttribute('aria-expanded', 'true');
      localStorage.setItem('sidebarCollapsed', 'false');
    }
  }

  const savedCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
  setCollapsed(savedCollapsed);

  collapseBtn.addEventListener('click', () => {
    setCollapsed(!sidebar.classList.contains('collapsed'));
  });

  collapseBtn.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      setCollapsed(!sidebar.classList.contains('collapsed'));
    }
  });
});
</script>
    <script src="../assets/js/chart.umd.min.js"></script>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/jquery.dataTables.min.js"></script>
    <script src="../assets/js/datatables.js"></script>
    <script src="../assets/js/laundry_system_js/laundryAddOrder.js"></script>
    <script src="../assets/js/gas_system_js/gasAddOrder.js"></script>
    <script src="../assets/js/orderlistDatatables.js?v=<?php echo time(); ?>"></script>
    <script src="../assets/js/laundry_system_js/pricing.js?v=<?php echo time(); ?>"></script>
    <script src="../assets/js/gas_system_js/gasPettyCash.js?v=<?php echo time(); ?>"></script>
    <script src="../assets/js/gas_system_js/gasStatusChange.js?v=<?php echo time(); ?>"></script>
    <script src="../assets/js/laundry_system_js/laundryStatusChange.js?v=<?php echo time(); ?>"></script>
</body>
</html>