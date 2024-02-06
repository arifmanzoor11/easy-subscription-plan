document.addEventListener("DOMContentLoaded", function() {
    // Add event listener to PayPal buttons
    var paypalButtons = document.querySelectorAll('.paypal-button');
    paypalButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var price = this.getAttribute('data-price');
            var planName = this.getAttribute('data-plan-name');
            var planId = this.getAttribute('data-plan-id');
            
            // Call the plan existence check function
            checkPlanExistence(price, planName, planId);
        });
    });

    // Function to check if plan exists before initiating PayPal payment
    function checkPlanExistence(price, planName, planId) {
        // AJAX call to check if plan exists
        jQuery.ajax({
            url: subscription_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'plan_validator',
                price: price,
                planName: planName,
                planId: planId
            },
            success: function(response) {
                if (response == true) {
                    // Plan exists, open PayPal dialog
                    openPayPalDialog(price, planName, planId);
                } else {
                    // Plan does not exist, handle accordingly
                    console.error('Plan does not exist in the database.');
                    // Display error message to the user
                    console.log('Plan does not exist in the database.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error checking plan existence:', error);
            }
        });
    }

    // Function to open PayPal dialog
    function openPayPalDialog(price, planName, planId) {
        var dialog = document.getElementById('paypal-dialog');
        dialog.style.display = "block";
        // Render PayPal button inside the dialog
        renderPayPalButton(price, planName, planId);
    }

    // Function to render PayPal button inside the dialog
    function renderPayPalButton(price, planName, planId) {
        var paypalButtonContainer = document.getElementById('paypal-button-container');
        paypalButtonContainer.innerHTML = '<div id="paypal-button"></div>';
        // Call initiatePayPalPayment function when PayPal button is clicked
        initiatePayPalPayment(price, planName);
    }

    // Function to initiate PayPal payment
    function initiatePayPalPayment(price, planName) {
        // Call PayPal SDK to initiate payment
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: price
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    handlePaymentSuccess(data.orderID, details, planName); // Pass planName to handlePaymentSuccess
                    console.log('Transaction ID: ' + data.orderID);
                    // Close the dialog after payment
                    closeDialog();
                });
            }
        }).render('#paypal-button');
    }

    // Function to handle payment success
    function handlePaymentSuccess(orderID, details, planName) {
        // Payment successful, handle confirmation here
        console.log('Transaction ID: ' + orderID);
        console.log('Amount: ' + details.purchase_units[0].amount.value);
        console.log('User Name: ' + details.payer.name.given_name + ' ' + details.payer.name.surname);

        // Preparing data for insertion
        var userId = subscription_ajax_object.current_user_id;
        var subscriptionName = planName; // Use the plan name passed from initiatePayPalPayment
        var amount = details.purchase_units[0].amount.value;
        var datetime = new Date().toISOString().slice(0, 19).replace('T', ' '); // Current date/time
        var planType = 'Your Plan Type'; // Replace with actual plan type
        var transactionId = orderID;
        var status = 'Completed'; // Assuming the transaction is successful

        // AJAX call to save transaction details in the database
        jQuery.ajax({
            url: subscription_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'save_transaction_details', // Custom AJAX action
                userId: userId,
                subscriptionName: subscriptionName,
                amount: amount,
                datetime: datetime,
                planType: planType,
                transactionId: transactionId,
                status: status,
                details: details
            },
            success: function(response) {
                // Display toast notification for transaction completion
                Toastify({
                    text: "Transaction completed",
                    duration: 3000,
                    gravity: "bottom",
                    position: "right",
                    backgroundColor: "linear-gradient(to right, #4CAF50, #006400)",
                    stopOnFocus: true
                }).showToast();
            },
            error: function(xhr, status, error) {
                console.error('Error saving transaction details:', error);
            }
        });
    }

    // Close dialog function
    function closeDialog() {
        var dialog = document.getElementById('paypal-dialog');
        dialog.style.display = "none";
    }

    // Close dialog when clicked outside the dialog
    window.onclick = function(event) {
        var dialog = document.getElementById('paypal-dialog');
        if (event.target == dialog) {
            closeDialog();
        }
    };

    // Close dialog when close button is clicked
    var closeButton = document.querySelector('.close');
    if (closeButton) {
        closeButton.addEventListener('click', function() {
            closeDialog();
        });
    }

    // Inject CSS for dialog styling
    var dialogCSS = `
    #paypal-dialog {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        z-index: 9999;
        max-height: 80%; /* Limiting the height of the dialog */
        overflow-y: auto; /* Enable vertical scrollbar */
    }

    .close {
        position: absolute;
        top: 10px;
        right: 10px;
        cursor: pointer;
        font-size: 20px;
        color: #999;
    }

    #paypal-button-container {
        text-align: center;
        padding-top: 25px;
        padding-left: 15px;
        padding-right: 15px;
    }
    `;
    
    var styleElement = document.createElement('style');
    styleElement.innerHTML = dialogCSS;
    document.head.appendChild(styleElement);
});
