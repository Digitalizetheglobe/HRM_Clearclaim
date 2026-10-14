<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Offline</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <h2>You are currently offline.</h2>
    <p>Please check your internet connection.</p>
    <p><a href="/">Back to the site</a></p>
    <script>
        if ('serviceWorker' in navigator && (location.hostname === '127.0.0.1' || location.hostname === 'localhost')) {
            navigator.serviceWorker.getRegistrations().then(function (registrations) {
                return Promise.all(registrations.map(function (registration) {
                    return registration.unregister();
                }));
            }).then(function () {
                location.replace('/');
            });
        }
    </script>
</body>
</html>
