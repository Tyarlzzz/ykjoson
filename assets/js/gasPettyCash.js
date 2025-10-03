    const options = { year: 'numeric', month: 'long', day: '2-digit' };
        document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', options);
    
    const input = document.getElementById("amountInput");

    function appendNumber(num) {
        // Prevent multiple decimal points
        if (num === "." && input.value.includes(".")) return;
        input.value += num;
    }

    function submitAmount() {
        let amount = parseFloat(input.value) || 0;

        // Update "Petty cash given"
        document.getElementById("pettyCashGiven").innerText = "₱" + amount.toLocaleString();

        // Get Today’s Total Sales from #sales
        let salesText = document.getElementById("sales").innerText;
        let sales = parseFloat(salesText.replace(/[₱,]/g, "")) || 0;

        // Recompute Total Amount
        let total = amount + sales;
        document.getElementById("totalAmount").innerText = "₱" + total.toLocaleString();

        // Clear input field after submit
        input.value = "";
    }


    function clearInput() {
        input.value = "";
    }
