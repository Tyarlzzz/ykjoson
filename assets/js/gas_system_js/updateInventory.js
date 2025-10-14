// Gas card selection and update functionality
const gasCards = document.querySelectorAll('.gas-card');
const updateForm = document.getElementById('updateForm');
const chooseText = document.getElementById('chooseText');
const updateBrandImage = document.getElementById('updateBrandImage');
const updateBrandName = document.getElementById('updateBrandName');
const stockInput = document.getElementById('stockInput');
const priceInput = document.getElementById('priceInput');
const cancelBtn = document.getElementById('cancelBtn');
const confirmBtn = document.getElementById('confirmBtn');

let selectedCard = null;
let selectedBrand = null;

// function to reset card styles
function resetCardStyles() {
    gasCards.forEach(card => {
        card.style.backgroundColor = '';
        card.style.borderColor = '';
    });
}

// Brand images
const brandImages = {
    petron: '../assets/images/petron.png',
    econo: '../assets/images/econo.png',
    seagas: '../assets/images/seagas.png'
};

// Brand names
const brandNames = {
    petron: 'Petron',
    econo: 'Econo',
    seagas: 'SeaGas'
};

// Card selection functionality
gasCards.forEach(card => {
    card.addEventListener('click', function () {
        const brand = this.dataset.brand;
        const currentStock = this.dataset.stock;
        const currentPrice = this.dataset.price;

        // Reset all cards styles
        resetCardStyles();

        // Highlight selected card
        this.style.backgroundColor = '#fee2e2'; // bg-red-100
        this.style.borderColor = '#fca5a5'; // border-red-300

        selectedCard = this;
        selectedBrand = brand;
        updateBrandImage.src = brandImages[brand];
        updateBrandImage.alt = brandNames[brand];
        updateBrandName.textContent = brandNames[brand];
        stockInput.value = currentStock;
        priceInput.value = currentPrice;

        // Show form for updating
        chooseText.classList.add('hidden');
        updateForm.classList.remove('hidden');
    });
});

// Cancel button
cancelBtn.addEventListener('click', function () {
    
    resetCardStyles();

    updateForm.classList.add('hidden');
    chooseText.classList.remove('hidden');

    // reset variables to unselect all cards
    selectedCard = null;
    selectedBrand = null;
});

// Confirm button functionality
confirmBtn.addEventListener('click', function () {
    if (!selectedCard || !selectedBrand) return;

    // Update stock and price in the card
    const newStock = parseInt(stockInput.value);
    const newPrice = parseInt(priceInput.value);

    selectedCard.dataset.stock = newStock;
    selectedCard.dataset.price = newPrice;

    // Update displayed values
    document.getElementById(`${selectedBrand}-stock`).textContent = newStock;
    document.getElementById(`${selectedBrand}-price`).textContent = newPrice;

    cancelBtn.click();
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