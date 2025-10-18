const options = { year: 'numeric', month: 'long', day: '2-digit' };
document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', options);
document.getElementById('receiptDate').textContent = new Date().toLocaleDateString('en-US', options);

const input = document.getElementById("amountInput");
const pettyCashElement = document.getElementById("pettyCashGiven");
const totalElement = document.getElementById("totalAmount");
const salesElement = document.getElementById("sales");

// Get today's date for localStorage key (YYYY-MM-DD)
const today = new Date().toISOString().split('T')[0];
const storageKey = `pettyCash_${riderId}_${today}`;

// Load petty cash from localStorage
function loadPettyCash() {
    const savedAmount = localStorage.getItem(storageKey);
    const amount = savedAmount ? parseFloat(savedAmount) : 0;
    pettyCashElement.innerHTML = "₱" + amount.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

    const salesText = salesElement.innerHTML;
    const sales = parseFloat(salesText.replace(/[₱,]/g, "")) || salesAmount;

    const total = amount + sales;
    totalElement.innerHTML = "₱" + total.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function savePettyCash(amount) {
    localStorage.setItem(storageKey, amount.toString());
}

function appendNumber(num) {
    if (num === "." && input.value.includes(".")) return;
    input.value += num;
}

function submitAmount() {
    let amount = parseFloat(input.value) || 0;
    pettyCashElement.innerHTML = "₱" + amount.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

    const salesText = salesElement.innerHTML;
    const sales = parseFloat(salesText.replace(/[₱,]/g, "")) || salesAmount;
    const total = amount + sales;
    totalElement.innerHTML = "₱" + total.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });

    savePettyCash(amount);
    input.value = "";
}

function clearInput() {
    input.value = "";
}

// ✅ UPDATED confirmPettyCash()
function confirmPettyCash() {
    const pettyCashText = pettyCashElement.textContent.replace(/[₱,]/g, '');
    const pettyCash = parseFloat(pettyCashText) || 0;

    if (pettyCash <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Amount Entered',
            text: 'Please enter and submit a petty cash amount first.'
        });
        return;
    }

    const riderId = parseInt(new URLSearchParams(window.location.search).get('rider_id')) || 1;

    Swal.fire({
        title: 'Add Petty Cash',
        text: `Add ₱${pettyCash.toLocaleString()} to this rider’s petty cash?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Add',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#19B900',
        cancelButtonColor: '#FF1D21'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('savePettyCash.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    rider_id: riderId,
                    petty_cash: pettyCash,
                    action: 'add'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // ✅ Update petty cash and totals dynamically
                    document.getElementById('currentPettyCash').textContent =
                        `Current Petty Cash: ₱${data.new_amount.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
                    salesElement.textContent =
                        `₱${data.total_sales.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
                    totalElement.textContent =
                        `₱${data.total_amount.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;

                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        html: `
                            <p>Previous: ₱${data.previous_amount.toLocaleString()}</p>
                            <p>New Petty Cash: <strong>₱${data.new_amount.toLocaleString()}</strong></p>
                            <p>Today's Sales: ₱${data.total_sales.toLocaleString()}</p>
                            <hr/>
                            <p>Total Amount: <strong>₱${data.total_amount.toLocaleString()}</strong></p>
                        `
                    }).then(() => {
                        document.getElementById('amountInput').value = '';
                        pettyCashElement.textContent = '₱0';
                        savePettyCash(0);
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to update petty cash.'
                    });
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: err.message
                });
            });
        }
    });
}

// ✅ The rest (clearPettyCash, deletePettyCash) stays the same
function clearPettyCash() {
    const currentDisplay = document.getElementById('currentPettyCash');
    const riderId = parseInt(new URLSearchParams(window.location.search).get('rider_id')) || 1;

    Swal.fire({
        title: 'Clear Petty Cash',
        text: 'This will reset this rider’s petty cash to ₱0. Continue?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Clear',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#FF1D21',
        cancelButtonColor: '#6B7280'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('savePettyCash.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    rider_id: riderId,
                    petty_cash: 0,
                    action: 'clear'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    currentDisplay.textContent = `Current Petty Cash: ₱0.00`;
                    salesElement.textContent = `₱${data.total_sales.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
                    totalElement.textContent = `₱${data.total_amount.toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
                    Swal.fire({ icon: 'success', title: 'Cleared!', text: 'Petty cash reset to ₱0 successfully.' });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                }
            })
            .catch(err => {
                Swal.fire({ icon: 'error', title: 'Network Error', text: err.message });
            });
        }
    });
}

function deletePettyCash() {
    pettyCashElement.innerHTML = '₱0';
    const salesText = salesElement.innerHTML.replace(/[₱,]/g, '');
    const sales = parseFloat(salesText) || salesAmount;
    const newTotal = sales;
    totalElement.innerHTML = '₱' + newTotal.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    savePettyCash(0);
}

loadPettyCash();
