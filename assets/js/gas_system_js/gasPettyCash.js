// pettycashJS.txt — robust version to avoid initial-zero flicker
(function () {
    'use strict';

    // Formatting options
    const moneyOptions = {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    };

    // Run after DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
        // Elements
        const input = document.getElementById("amountInput");
        const pettyCashElement = document.getElementById("pettyCashGiven");
        const totalElement = document.getElementById("totalAmount");
        const salesElement = document.getElementById("sales");
        const currentPettyEl = document.getElementById("currentPettyCash");

        // Defensive checks
        if (!pettyCashElement || !totalElement || !salesElement) {
            console.error('PettyCash script: required DOM elements not found.', {
                pettyCashElement,
                totalElement,
                salesElement
            });
            return;
        }

        // Debug info: check PHP-provided globals (if any)
        console.info('PettyCash debug:', {
            salesElement_initial: salesElement.innerText,
            salesAmount_global: typeof salesAmount !== 'undefined' ? salesAmount : null,
            riderId_global: typeof riderId !== 'undefined' ? riderId : null
        });

        // ----- loadPettyCash: keep PHP-rendered values intact on initial load -----
        // function loadPettyCash() {
        //     // Reset only the petty-cash input display (not the totals from backend)
        //     pettyCashElement.innerHTML = "₱0";

        //     // If PHP already rendered a sales/total value into the page, keep it.
        //     // Prefer the actual salesElement innerText if it's non-empty and not zero-like.
        //     const salesTextRaw = (salesElement.innerText || salesElement.textContent || '').trim();
        //     const salesNumeric = parseFloat(salesTextRaw.replace(/[₱,\s]/g, ''));
        //     if (salesTextRaw && !Number.isNaN(salesNumeric) && salesNumeric !== 0) {
        //         // Use the existing PHP-rendered sales value, and format total accordingly
        //         totalElement.innerHTML = salesElement.innerHTML;
        //         console.log('loadPettyCash: preserved PHP-rendered sales/total:', salesTextRaw);
        //         return;
        //     }

        //     // Fallback: try using global salesAmount (if provided by PHP)
        //     if (typeof salesAmount !== 'undefined' && !Number.isNaN(Number(salesAmount))) {
        //         const formatted = "₱" + Number(salesAmount).toLocaleString('en-US', moneyOptions);
        //         salesElement.innerHTML = formatted;
        //         totalElement.innerHTML = formatted;
        //         console.log('loadPettyCash: used global salesAmount fallback:', salesAmount);
        //         return;
        //     }

        //     // Final fallback: keep totalElement as-is (do not force zero)
        //     console.warn('loadPettyCash: could not detect sales value; leaving PHP values intact.');
        // }

        // Append keypad number
        function appendNumber(num) {
            if (!input) return;
            if (num === "." && input.value.includes(".")) return;
            input.value += num;
        }

        // Submit (local calculation only — not saved to DB)
        function submitAmount() {
            if (!input) return;
            const amount = parseFloat(input.value) || 0;

            pettyCashElement.innerHTML = "₱" + amount.toLocaleString('en-US', { minimumFractionDigits: 2 });
            // Parse sales from the page (trusted source)
            const salesText = (salesElement.innerText || salesElement.textContent || '').replace(/[₱,\s]/g, '');
            const sales = parseFloat(salesText) || 0;
            const total = amount + sales;
            totalElement.innerHTML = "₱" + total.toLocaleString('en-US', moneyOptions);

            input.value = '';
        }

        function clearInput() {
            if (input) input.value = '';
        }

        // Confirm: send to savePettyCash.php and update UI from server response
        function confirmPettyCash() {
            const pettyCashText = (pettyCashElement.textContent || '').replace(/[₱,]/g, '').trim();
            const pettyCash = parseFloat(pettyCashText) || 0;

            if (pettyCash <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Amount Entered',
                    text: 'Please enter and submit a petty cash amount first.'
                });
                return;
            }

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
                if (!result.isConfirmed) return;

                // Ensure riderId exists
                const rid = (typeof riderId !== 'undefined' && riderId) ? riderId : (parseInt(new URLSearchParams(window.location.search).get('rider_id')) || null);
                if (!rid) {
                    Swal.fire({ icon: 'error', title: 'No Rider', text: 'Rider ID not found.' });
                    console.error('confirmPettyCash: missing riderId', { riderId, url_rider: new URLSearchParams(window.location.search).get('rider_id') });
                    return;
                }

                // Send POST
                fetch('savePettyCash.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        rider_id: rid,
                        petty_cash: pettyCash,
                        action: 'add'
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (!data) throw new Error('Empty response from server');
                        if (!data.success) {
                            throw new Error(data.message || 'Failed to update petty cash');
                        }

                        // The server should return:
                        // { success: true, previous_amount, new_amount, total_sales, total_amount }
                        // Update the UI using server-provided authoritative values
                        if (currentPettyEl) {
                            currentPettyEl.textContent = `Current Petty Cash: ₱${Number(data.new_amount).toLocaleString('en-US', moneyOptions)}`;
                        }

                        if (typeof data.total_sales !== 'undefined') {
                            salesElement.textContent = `₱${Number(data.total_sales).toLocaleString('en-US', moneyOptions)}`;
                        }

                        if (typeof data.total_amount !== 'undefined') {
                            totalElement.textContent = `₱${Number(data.total_amount).toLocaleString('en-US', moneyOptions)}`;
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            html: `
                            <p>Previous: ₱${Number(data.previous_amount).toLocaleString('en-US', moneyOptions)}</p>
                            <p>New Petty Cash: <strong>₱${Number(data.new_amount).toLocaleString('en-US', moneyOptions)}</strong></p>
                            <p>Today's Sales: ₱${Number(data.total_sales).toLocaleString('en-US', moneyOptions)}</p>
                            <hr/>
                            <p>Total Amount (All Riders + Sales): <strong>₱${Number(data.total_amount).toLocaleString('en-US', moneyOptions)}</strong></p>
                        `
                        }).then(() => {
                            // Reset input UI
                            if (input) input.value = '';
                            pettyCashElement.textContent = '₱0';
                        });
                    })
                    .catch(err => {
                        console.error('confirmPettyCash error:', err);
                        Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Network or server error' });
                    });
            });
        }

        // Clear petty cash (server-side)
        function clearPettyCash() {
            const rid = (typeof riderId !== 'undefined' && riderId) ? riderId : (parseInt(new URLSearchParams(window.location.search).get('rider_id')) || null);
            if (!rid) {
                Swal.fire({ icon: 'error', title: 'No Rider', text: 'Rider ID not found.' });
                return;
            }

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
                if (!result.isConfirmed) return;

                fetch('savePettyCash.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        rider_id: rid,
                        petty_cash: 0,
                        action: 'clear'
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (!data || !data.success) throw new Error(data?.message || 'Failed to clear petty cash');

                        if (currentPettyEl) currentPettyEl.textContent = `Current Petty Cash: ₱0.00`;
                        if (typeof data.total_sales !== 'undefined') salesElement.textContent = `₱${Number(data.total_sales).toLocaleString('en-US', moneyOptions)}`;
                        if (typeof data.total_amount !== 'undefined') totalElement.textContent = `₱${Number(data.total_amount).toLocaleString('en-US', moneyOptions)}`;

                        Swal.fire({ icon: 'success', title: 'Cleared!', text: 'Petty cash reset to ₱0 successfully.' });
                    })
                    .catch(err => {
                        console.error('clearPettyCash error:', err);
                        Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'Network or server error' });
                    });
            });
        }

        // Delete petty cash UI (local only)
        function deletePettyCash() {
            pettyCashElement.innerHTML = '₱0';
            const salesText = (salesElement.innerText || salesElement.textContent || '').replace(/[₱,]/g, '');
            const sales = parseFloat(salesText) || 0;
            const newTotal = sales;
            totalElement.innerHTML = '₱' + newTotal.toLocaleString('en-US', moneyOptions);
        }

        // Expose handlers to global (so buttons with onclick can call them)
        window.appendNumber = appendNumber;
        window.submitAmount = submitAmount;
        window.clearInput = clearInput;
        window.confirmPettyCash = confirmPettyCash;
        window.clearPettyCash = clearPettyCash;
        window.deletePettyCash = deletePettyCash;

        // Finally call loader
        loadPettyCash();
    });
})();
