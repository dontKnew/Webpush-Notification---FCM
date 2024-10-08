import { initializeApp } from "https://www.gstatic.com/firebasejs/9.0.0/firebase-app.js";
import { getMessaging, getToken, onMessage } from "https://www.gstatic.com/firebasejs/9.0.0/firebase-messaging.js";

if ('serviceWorker' in navigator) {
    const timestamp = new Date().getTime(); 
    navigator.serviceWorker.register(`/firebase-messaging-sw.js?time=${timestamp}`) // Append timestamp to force update
    .then(registration => {
        logMessage('Service worker registered successfully');
    })
    .catch(error => {
        logMessage(`Service worker registration failed: ${error}`);
    });
}

// Firebase configuration
const firebaseConfig = {
   
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

// Call requestNotificationPermission to prompt user for permission
requestNotificationPermission();

// Listen for messages when the app is in the foreground
// onMessage(messaging, (payload) => {
//     console.log('foreground message: ', payload);
//     logMessage(JSON.stringify(payload));
//     const dataFromServer = payload.notification || {};
//     const notificationTitle = dataFromServer.title || "No title available";
//     const notificationBody = dataFromServer.body || "No body available";
//     const notificationImage = dataFromServer.image || "https://developers.elementor.com/docs/assets/img/elementor-placeholder-image.png"; // Fallback image
//     const notificationUrl = payload.data?.url || "https://phpmaster.in/notfound";

//     const notificationOptions = {
//         body: notificationBody,
//         image: notificationImage,
//         icon: "https://phpmaster.in/favicon.png",
//         data: {
//             url: notificationUrl
//         }
//     };

//     // Show notification using the service worker
//     self.registration.showNotification(notificationTitle, notificationOptions);
// });


// Listen for messages when the app is in the foreground



onMessage(messaging, (payload) => {
    console.log('Foreground message: ', payload);
    logMessage(JSON.stringify(payload));

    const dataFromServer = payload.notification || {};
    const notificationTitle = dataFromServer.title || "No title available";
    const notificationBody = dataFromServer.body || "No body available";
    const notificationImage = dataFromServer.image || "https://developers.elementor.com/docs/assets/img/elementor-placeholder-image.png"; // Fallback image
    const notificationUrl = payload.data?.url || "https://phpmaster.in/notfound";

    // Show the custom UI notification
    showNotificationModal(notificationTitle, notificationBody, notificationImage, notificationUrl);
});

function showNotificationModal(title, body, image, url) {
    const modal = document.getElementById('notification-modal');
    const modalTitle = document.getElementById('notification-title');
    const modalBody = document.getElementById('notification-body');
    const modalImage = document.getElementById('notification-image');
    const overlay = document.getElementById('overlay'); // Get the overlay element

    // Set the notification content
    modalTitle.innerText = title;
    modalBody.innerText = body;
    modalImage.src = image;

    // Display the overlay and modal
    overlay.style.display = 'flex'; // Show overlay
    modal.style.display = 'flex'; // Show modal

    // Close modal when the close button is clicked
    document.getElementById('notification-close').onclick = function () {
        closeNotificationModal();
    };

    // Close modal when overlay is clicked
    overlay.onclick = function () {
        closeNotificationModal();
    };

    // Redirect to the URL when the modal itself is clicked
    modal.onclick = function () {
        window.location.href = url;
    };
}

function closeNotificationModal() {
    const modal = document.getElementById('notification-modal');
    const overlay = document.getElementById('overlay'); // Get the overlay element
    modal.style.display = 'none'; // Hide modal
    overlay.style.display = 'none'; // Hide overlay
}





// Custom function to request notification permission
function requestNotificationPermission() {
    if (Notification.permission === 'default' || Notification.permission === 'denied') {
        showNotificationPermissionModal();
    } else if (Notification.permission === 'granted') {
        logMessage('Notification permission already granted');
        processToken(); 
    }
}

function showNotificationPermissionModal() {
    const modal = document.getElementById('notification-permission-modal');
    modal.style.display = 'flex';

    document.getElementById('allow-notifications').onclick = function () {
        Notification.requestPermission().then(permission => {
            modal.style.display = 'none'; // Hide the modal
            if (permission === 'granted') {
                window.location.reload();
                logMessage('Notification permission granted');
                processToken();
            } else {
                logMessage('Notification permission denied');
            }
        });
    };

    document.getElementById('deny-notifications').onclick = function () {
        modal.style.display = 'none'; // Hide the modal
        logMessage('Notification permission denied');
    };
}

// Process Firebase token
function processToken() {
    getToken(messaging, { vapidKey: 'BC6tqEAapb2mEBhm4t3KxZjfouxXs2j328WGgz6LA8ivqCUM6khxJvNfPBJjTtpJXglPj4n14I1WciJlL-eHRuk' })
        .then((currentToken) => {
            if (currentToken) {
                document.getElementById("token").innerText = currentToken;
                logMessage('Token retrieved successfully');
                sendTokenToServer(currentToken);
            } else {
                logMessage('No registration token available.');
            }
        })
        .catch((err) => {
            logMessage(`Error getting token: ${err}`);
        });
}

// Send token to server
function sendTokenToServer(token) {
    const deviceDetails = getDeviceDetails();
    fetch('./save_token.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            token: token,
            device_name: deviceDetails.osName,
            device_type: deviceDetails.deviceType,
            page_url: deviceDetails.pageUrl
        })
    })
    .then(response => response.json())
    .then(data => {
        logMessage(`Token sent to server: ${JSON.stringify(data)}`);
    })
    .catch(error => {
        logMessage(`Error sending token to server: ${error}`);
    });
}

function getDeviceDetails() {
    const userAgent = navigator.userAgent;
    let deviceType = 'Desktop'; 
    let osName = 'Unknown OS';
    if (/Mobi|Android/i.test(userAgent)) {
        deviceType = 'Mobile';
    } else if (/Tablet|iPad/i.test(userAgent)) {
        deviceType = 'Tablet';
    }

    if (/Windows NT/i.test(userAgent)) {
        osName = 'Windows';
    } else if (/Mac OS X/i.test(userAgent)) {
        osName = 'MacOS';
    } else if (/Android/i.test(userAgent)) {
        osName = 'Android';
    } else if (/iPhone|iPad/i.test(userAgent)) {
        osName = 'iOS';
    } else if (/Linux/i.test(userAgent)) {
        osName = 'Linux';
    }

    return {
        deviceType: deviceType,
        osName: osName,
        pageUrl: window.location.href 
    };
}

// Log function to update UI
function logMessage(message) {
    const logContainer = document.getElementById('logs');
    const logEntry = document.createElement('div');
    logEntry.className = 'log-entry';
    logEntry.textContent = message;
    logContainer.appendChild(logEntry);
    logContainer.scrollTop = logContainer.scrollHeight; // Auto-scroll
}


