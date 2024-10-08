importScripts('https://www.gstatic.com/firebasejs/10.5.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.5.0/firebase-messaging-compat.js');

const firebaseConfig = {
    
};

firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// Handle background notifications
messaging.onBackgroundMessage(function(payload) {
    console.log('background message: ', payload);
    const dataFromServer = payload.notification || {};
    const notificationTitle = dataFromServer.title || "No title available";
    const notificationBody = dataFromServer.body || "No body available";
    const notificationImage = dataFromServer.image || "https://developers.elementor.com/docs/assets/img/elementor-placeholder-image.png"; // Fallback image
    const notificationUrl = payload.data?.url || "https:/phpmaster.in/notfound";

    const notificationOptions = {
        body: notificationBody,
        image: notificationImage,
        icon: "https://phpmaster.in/favicon.png",
        data: {
            url: notificationUrl
        }
    };
    return self.registration.showNotification(notificationTitle, notificationOptions);
});

self.addEventListener('notificationclick', function(event) {
    console.log("Notification clicked", event);
    event.notification.close(); // Close the notification
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clientList) {
            console.log("Client list: ", clientList);
            for (let i = 0; i < clientList.length; i++) {
                let client = clientList[i];
                console.log("Client URL: ", client.url);
                if (client.url === event.notification.data.url && 'focus' in client) {
                    return client.focus();
                }
            }
            if (clients.openWindow) {
                console.log("Opening new window: ", event.notification.data.url);
                return clients.openWindow(event.notification.data.url);
            }
        })
    );
});


