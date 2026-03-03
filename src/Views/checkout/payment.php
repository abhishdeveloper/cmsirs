<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment...</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
</head>
<body style="background-color: #f3f4f6; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; font-family: sans-serif;">

    <div style="text-align: center; background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <h2 style="color: #1f2937; margin-bottom: 20px;">Secure Payment</h2>
        <p style="color: #6b7280; margin-bottom: 30px;">Please wait while we open the payment gateway...</p>
        <button id="rzp-button1" style="background-color: #2563eb; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; cursor: pointer;">Pay Now</button>
    </div>

    <!-- The form that will submit to verify payment -->
    <form name='razorpayform' action="/checkout/verify" method="POST">
        <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
        <input type="hidden" name="razorpay_signature"  id="razorpay_signature" >
        <input type="hidden" name="razorpay_order_id"   id="razorpay_order_id" value="<?php echo $razorpayOrder['id']; ?>">
    </form>

    <script>
        // Razorpay Checkout Configuration
        var options = {
            "key": "<?php echo $razorpayKey; ?>", // Enter the Key ID generated from the Dashboard
            "amount": "<?php echo $amountInPaise; ?>", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
            "currency": "INR",
            "name": "MediCare",
            "description": "Order Payment",
            "image": "https://example.com/your_logo.jpg", // Replace with actual logo URL if available
            "order_id": "<?php echo $razorpayOrder['id']; ?>",
            "handler": function (response){
                // Place the IDs into the form and submit it to our server for verification
                document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id;
                document.getElementById('razorpay_signature').value = response.razorpay_signature;
                document.razorpayform.submit();
            },
            "prefill": {
                "name": "<?php echo $userName; ?>",
                "email": "<?php echo $userEmail; ?>",
                "contact": "<?php echo $userPhone; ?>"
            },
            "notes": {
                "address": "Razorpay Corporate Office"
            },
            "theme": {
                "color": "#2563eb"
            }
        };

        var rzp1 = new Razorpay(options);

        // Handle payment failure event
        rzp1.on('payment.failed', function (response){
                alert("Payment Failed. Reason: " + response.error.description);
                // Optionally redirect to cart or checkout failure page
                window.location.href = '/checkout?error=' + encodeURIComponent(response.error.description);
        });

        // Open checkout immediately when page loads
        document.getElementById('rzp-button1').onclick = function(e){
            rzp1.open();
            e.preventDefault();
        }

        // Auto trigger the button click
        window.onload = function() {
             document.getElementById('rzp-button1').click();
        };
    </script>
</body>
</html>
