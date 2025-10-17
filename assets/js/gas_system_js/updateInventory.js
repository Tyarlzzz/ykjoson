// Gas card selection and update functionality
const gasCards = document.querySelectorAll('.gas-card');
const updateForm = document.getElementById('updateForm');
const chooseText = document.getElementById('chooseText');
const updateBrandImage = document.getElementById('updateBrandImage');
const updateBrandName = document.getElementById('updateBrandName');
const stockInput = document.getElementById('stockInput');
const priceInput = document.getElementById('priceInput');
const brandInput = document.getElementById('brandInput');
const cancelBtn = document.getElementById('cancelBtn');

let selectedCard = null;
let selectedBrand = null;

// function to reset card styles
function resetCardStyles() {
    gasCards.forEach(card => {
        card.style.backgroundColor = '';
        card.style.borderColor = '';
    });
}

const brandImages = {
    petron: '../assets/images/petron.png',
    econo: '../assets/images/econo.png',
    seagas: '../assets/images/seagas.png'
};

const brandNames = {
    petron: 'Petron',
    econo: 'Econo',
    seagas: 'SeaGas'
};

// Low stock thresholds
const lowStockThresholds = {
    petron: 50,
    econo: 50,
    seagas: 15
};

// Card selection handler
gasCards.forEach(card => {
    card.addEventListener('click', function () {
        const brand = this.dataset.brand;
        const currentStock = this.dataset.stock;
        const currentPrice = this.dataset.price;

        resetCardStyles();

        // Highlight selected card
        this.style.backgroundColor = '#fee2e2'; // bg-red-100
        this.style.borderColor = '#fca5a5'; // border-red-300

        selectedCard = this;
        selectedBrand = brand;
        updateBrandImage.src = brandImages[brand];
        updateBrandImage.alt = brandNames[brand];
        updateBrandName.textContent = brandNames[brand];
        brandInput.value = brand;
        stockInput.value = currentStock;
        priceInput.value = currentPrice;

        // Show form for updating
        chooseText.classList.add('hidden');
        updateForm.classList.remove('hidden');
    });
});

cancelBtn.addEventListener('click', function () {
    
    resetCardStyles();

    updateForm.classList.add('hidden');
    chooseText.classList.remove('hidden');

    // reset variables to unselect all cards
    selectedCard = null;
    selectedBrand = null;
});

// Automatically converts negative inputs to zero
priceInput.addEventListener('input', function () {
    if (this.value < 0) {
        this.value = 0;
    }
});

// Automatically converts negative inputs to zero
stockInput.addEventListener('input', function () {
    if (this.value < 0) {
        this.value = 0;
    }
});

// Function to update low stock warning
function updateLowStockWarning(brand, stock) {
    const card = document.querySelector(`.gas-card[data-brand="${brand}"]`);
    const warningDiv = card.querySelector('.low-stock-warning');
    
    if (!warningDiv) return;   // If element doesn't exist, exit
    
    // Check stocks if is <= threshold
    if (stock <= lowStockThresholds[brand]) {
        warningDiv.classList.remove('hidden');
    } else {
        warningDiv.classList.add('hidden');
    }
}

// Initialize low stock warnings on page load
document.addEventListener('DOMContentLoaded', function() {
    gasCards.forEach(card => {
        const brand = card.dataset.brand;
        const stock = parseInt(card.dataset.stock);
        updateLowStockWarning(brand, stock);
    });
});