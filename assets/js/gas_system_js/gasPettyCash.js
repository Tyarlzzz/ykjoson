const options = { year: 'numeric', month: 'long', day: '2-digit' };
document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', options);
document.getElementById('receiptDate').textContent = new Date().toLocaleDateString('en-US', options);

const input = document.getElementById("amountInput");
const pettyCashElement = document.getElementById("pettyCashGiven");
const totalElement = document.getElementById("totalAmount");

// Get today's date for localStorage key (YYYY-MM-DD)
const today = new Date().toISOString().split('T')[0];
const storageKey = `pettyCash_${riderId}_${today}`;

// Debug: Log the storage key (remove this later)
console.log('Storage key:', storageKey);
console.log('Rider ID:', riderId);

// Helper: Load petty cash from localStorage and update display
function loadPettyCash() {
    const savedAmount = localStorage.getItem(storageKey);
    console.log('Saved amount from localStorage:', savedAmount); // Debug
    const amount = savedAmount ? parseFloat(savedAmount) : 0;
    console.log('Parsed amount:', amount); // Debug

    // Update "Petty cash given"
    pettyCashElement.innerHTML = "₱" + amount.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

    // Get sales (fallback to PHP var)
    const salesText = document.getElementById("sales").innerHTML;
    const sales = parseFloat(salesText.replace(/[₱,]/g, "")) || salesAmount;
    console.log('Sales amount:', sales); // Debug

    // Recompute Total Amount
    const total = amount + sales;
    totalElement.innerHTML = "₱" + total.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    
    console.log('Loaded and updated display with petty cash:', amount); // Debug
}

// Helper: Save petty cash to localStorage
function savePettyCash(amount) {
    localStorage.setItem(storageKey, amount.toString());
    console.log('Petty cash saved to localStorage:', amount, 'Key:', storageKey); // Debug
}

function appendNumber(num) {
    // Prevent multiple decimal points
    if (num === "." && input.value.includes(".")) return;
    input.value += num;
}

function submitAmount() {
    let amount = parseFloat(input.value) || 0;
    console.log('Submitting amount:', amount); // Debug

    // Update "Petty cash given"
    pettyCashElement.innerHTML = "₱" + amount.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

    // Get Today’s Total Sales from #sales
    const salesText = document.getElementById("sales").innerHTML;
    const sales = parseFloat(salesText.replace(/[₱,]/g, "")) || salesAmount;

    // Recompute Total Amount
    const total = amount + sales;
    totalElement.innerHTML = "₱" + total.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

    // Save to localStorage
    savePettyCash(amount);

    // Clear input field after submit
    input.value = "";
}

function clearInput() {
    input.value = "";
}

function confirmPettyCash() {
    // Reset petty cash given to zero
    pettyCashElement.innerHTML = '₱0';

    const salesText = document.getElementById('sales').innerHTML.replace('₱', '').replace(/,/g, '');
    const sales = parseFloat(salesText) || salesAmount;
    const newTotal = sales + 0; // Petty cash is now 0

    totalElement.innerHTML = '₱' + newTotal.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    
    // Save to localStorage
    savePettyCash(0);
    
    alert('Petty cash confirmed and reset to ₱0!');
}

function deletePettyCash() {
    // Reset petty cash given to zero
    pettyCashElement.innerHTML = '₱0';

    const salesText = document.getElementById('sales').innerHTML.replace('₱', '').replace(/,/g, '');
    const sales = parseFloat(salesText) || salesAmount;
    const newTotal = sales + 0; // Petty cash is now 0
    
    // Update total display
    totalElement.innerHTML = '₱' + newTotal.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    
    // Save to localStorage
    savePettyCash(0);
}

// Load saved petty cash immediately (DOM is ready since script is at bottom)
loadPettyCash();
