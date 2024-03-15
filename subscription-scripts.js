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
        jQuery(".subscription-plans").after('<div class="loader-container"><div class="loader"></div>Checking, please wait.</div>');
        jQuery.ajax({
            url: subscription_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'plan_validator',
                price: price,
                planName: planName,
                planId: planId,
                dataType: "JSON",
            },
            success: function(response) {
                var objJSON = JSON.parse(response);

                if (objJSON.Code == '200') {
                    // Plan exists, open PayPal dialog
                    jQuery.ajax({
                        url: subscription_ajax_object.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'user_has_bought_plan',
                        },
                        success: function(bought_plan_response) {
                            var objJSON = JSON.parse(bought_plan_response);
                            // console.log(objJSON_bought.code);
                            if (objJSON.Code == '201') {
                                // User has bought the plan, open PayPal dialog                                
                                openPayPalDialog(price, planName, planId);
                            } 
                            else if (objJSON.Code == '203')  {
                                // User has already bought the plan, handle accordingly
                                // console.log('User has already bought the plan');
                                Toastify({
                                    text: "You Have Already Purchased A Plan",
                                    duration: 4000,
                                    gravity: "bottom",
                                    position: "right",
                                    background: "linear-gradient(to right, #4CAF50, #006400)",
                                    stopOnFocus: true
                                }).showToast();
                            }
                            else if(response == '404') {
                                // User is not logged in, handle accordingly
                                console.log('User is not logged in');
                                // window.location.href = '/wp-admin';
                                changeView('no-authentication');
                                // Display error message to the user or handle it as needed
                            } 
                        },
                        error: function(xhr, status, error) {
                            console.error('Error checking if user has bought the plan:', error);
                        }
                    });
                    

                } else {
                    // Plan does not exist, handle accordingly
                    console.error('Plan does not exist in the database.');
                    // Display error message to the user
                    console.log('Plan does not exist in the database.');
                }
                jQuery(".loader-container").hide();
            },
            error: function(xhr, status, error) {
                console.error('Error checking plan existence:', error);
                jQuery(".loader-container").hide();
            }
            
        
        });
        
    }

    function changeView(view) {
        window.history.pushState({ path: view }, '', '?plan=' + view);
        location.reload();
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
        initiatePayPalPayment(price, planName, planId);
    }

    // Function to initiate PayPal payment
    function initiatePayPalPayment(price, planName, planId) {
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
                    handlePaymentSuccess(data.orderID, details, planName, planId); // Pass planName to handlePaymentSuccess
                    console.log('Transaction ID: ' + data.orderID);
                    // Close the dialog after payment
                    closeDialog();
                });
            }
        }).render('#paypal-button');
    }

    // Function to handle payment success
    function handlePaymentSuccess(orderID, details, planName, planId) {
        // Payment successful, handle confirmation here
        // console.log('Transaction ID: ' + orderID);
        // console.log('Amount: ' + details.purchase_units[0].amount.value);
        // console.log('User Name: ' + details.payer.name.given_name + ' ' + details.payer.name.surname);

        // Preparing data for insertion
        // var userId = subscription_ajax_object.current_user_id;
        var subscriptionName = planName; // Use the plan name passed from initiatePayPalPayment
        var amount = details.purchase_units[0].amount.value;
        var datetime = new Date().toISOString().slice(0, 19).replace('T', ' '); // Current date/time
        // var planType = 'Your Plan Type'; // Replace with actual plan type
        var transactionId = orderID;
        var status = 'Completed'; // Assuming the transaction is successful
        var planId = planId;

        // AJAX call to save transaction details in the database
        jQuery.ajax({
            url: subscription_ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'save_transaction_details', // Custom AJAX action
                // userId: userId,
                subscriptionName: subscriptionName,
                amount: amount,
                datetime: datetime,
                // planType: planType,
                transactionId: transactionId,
                status: status,
                details: details,
                planid: planId
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