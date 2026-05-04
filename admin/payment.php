



<button id="rzp-button1">Pay</button>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
var options = {
    // "key": "QYfufWITKwZduLXN3UEaKiJk", // Enter the Key ID generated from the Dashboard
    "key": "rzp_live_16EiPycQfSmnxY",
    "amount": "100", // Amount is in currency subunits. Default currency is INR. Hence, 50000 refers to 50000 paise
    "currency": "INR",
    "name": "DMBOSSONLINE", //your business name
    "description": "Test Transaction",
    "image": "https://dmbossonline.com/admin/dist/img/dmboss.jpg",
    // "order_id": "rzp_test_2SIVxtP10tEIDa", //This is a sample Order ID. Pass the `id` obtained in the response of Step 1
    // "order_id": "rzp_live_16EiPycQfSmnxY",
    "handler": function (response){
        alert(response.razorpay_payment_id);
        alert(response.razorpay_order_id);
        alert(response.razorpay_signature);
        //  console.log(response.error);
        // console.log(response);
    },
    "prefill": { //We recommend using the prefill parameter to auto-fill customer's contact information, especially their phone number
        "name": "TEST", //your customer's name
        "email": "TEST@example.com", 
        "contact": "9000090000"  //Provide the customer's phone number for better conversion rates 
    },
    "notes": {
        "address": "Razorpay Corporate Office"
    },
    "theme": {
        "color": "#3399cc"
    }
};
var rzp1 = new Razorpay(options);
rzp1.on('payment.failed', function (response){
        // alert(response.error.code);
        // alert(response.error.description);
        // alert(response.error.source);
        // alert(response.error.step);
        // alert(response.error.reason);
        // alert(response.error.metadata.order_id);
        // alert(response.error.metadata.payment_id);
        console.log(response);
});
document.getElementById('rzp-button1').onclick = function(e){
    rzp1.open();
    e.preventDefault();
}
</script>



















