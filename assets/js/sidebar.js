const switchSystem = document.querySelector('.switchSystem');
const switchSystemText = document.getElementById('switchSystem');
const sidebar = document.getElementById('sidebar');
const collapseBtn = document.getElementById('collapseBtn');
const collapseIcon = document.getElementById('collapseIcon');

let isGas = localStorage.getItem("isGas") === "true"; // Load saved state

function applySystemState() {
    if (isGas) {
        sidebar.classList.add("gas-mode");
        switchSystemText.textContent = "Laundry System";
        document.title = "Gas System";

    } else {
        sidebar.classList.remove("gas-mode");
        switchSystemText.textContent = "Gas System";
        document.title = "Laundry System";
    }
}

// Run on load
applySystemState();

switchSystem.addEventListener('click', (e) => {
    e.preventDefault();
    isGas = !isGas;
    localStorage.setItem("isGas", isGas); // Save state
    applySystemState();
});

function setCollapsed(collapsed) {
    if (collapsed) {
        sidebar.classList.add('collapsed');
        collapseIcon.classList.add('rotate-180');
        collapseBtn.setAttribute('aria-expanded', 'false');
        localStorage.setItem('sidebarCollapsed', '1');
    } else {
        sidebar.classList.remove('collapsed');
        collapseIcon.classList.remove('rotate-180');
        collapseBtn.setAttribute('aria-expanded', 'true');
        localStorage.setItem('sidebarCollapsed', '0');
    }
}

// Load previous collapse state
const saved = localStorage.getItem('sidebarCollapsed') === '1';
setCollapsed(saved);

// Button click
collapseBtn.addEventListener('click', () => {
    setCollapsed(!sidebar.classList.contains('collapsed'));
});

// Keyboard accessibility
collapseBtn.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        setCollapsed(!sidebar.classList.contains('collapsed'));
    }
});
