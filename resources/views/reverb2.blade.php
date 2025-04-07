<!-- resources/views/test-pusher.blade.php -->
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تست Pusher</title>
    <!-- لود فایل‌های کامپایل‌شده توسط Vite -->
    @vite(['resources/js/app.js'])
    <!-- توکن CSRF برای احراز هویت -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<h1>تست اتصال به Pusher</h1>
<div id="messages"></div>
<button id="acceptButton" style="display:none;">قبول درخواست</button>

<script type="module">
    let currentRequestId = null;
    window.Echo.private('drivers.2')
        .listen('.ride.request', (e) => {
            console.log('رویداد دریافت شد: ', e);
            currentRequestId = e.requestId;
            document.getElementById('messages').innerHTML += '<p>درخواست جدید داری</p>';
            document.getElementById('acceptButton').style.display = 'block';
        })
        .listen('.rideRequest.confirmedByOthers', (e) => {
            console.log('سرور گفت: ', e);
            document.getElementById('messages').innerHTML += '<p>درخواست توسط راننده دیگری قبول شد.</p>';
            document.getElementById('acceptButton').style.display = 'none'; // دکمه رو مخفی کن
        })
        .listen('.rideRequest.confirmed', (e) => {
            console.log('سرور گفت: ', e);
            document.getElementById('messages').innerHTML += '<p>درخواست قبول شد.</p>';
            document.getElementById('acceptButton').style.display = 'none'; // دکمه رو مخفی کن
        })
        .subscribed(() => {
            console.log('با موفقیت به کانال راننده ۲ وصل شدم');
            document.getElementById('messages').innerHTML += '<p>وصل شدم به کانال</p>';
        })
        .error((error) => {
            console.log('خطا توی سابسکرایب: ', error);
            document.getElementById('messages').innerHTML += '<p>خطای سابسکرایب: ' + error + '</p>';
        });

    function acceptRequest() {
        if (!currentRequestId) {
            alert('هیچ درخواستی نیست!');
            return;
        }

        // ارسال رویداد از کلاینت به سرور
        // const channel = Echo.connector.pusher.channel('private-drivers.8');
        // channel.trigger('client-request-accepted', {
        //     requestId: currentRequestId,
        //     driverId: 8 // یا ID راننده فعلی
        // });

        // Echo.connector.pusher.send_event(
        //     'riderAcceptRequest',   // Event Name
        //     {
        //         requestId: currentRequestId,
        //         driverId: 8 // یا ID راننده فعلی
        //     },  // data
        //     'private-driver.8'
        // );

        Echo.connector.pusher.send_event(
            'riderAcceptRequest',   // Event Name
            {
                requestId: currentRequestId,
                driverId: 2 // یا ID راننده فعلی
            },  // data
            'private-driver.2',
        );

        // Echo.private('drivers.8').whisper('accepted', {
        //     requestId: currentRequestId,
        //     driverId: 8 // یا ID راننده فعلی
        // });

        document.getElementById('acceptButton').style.display = 'none'; // دکمه رو مخفی کن
        currentRequestId = null; // ریست کردن
    }

    document.getElementById('acceptButton').addEventListener('click', acceptRequest);

</script>
</body>
</html>
