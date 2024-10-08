<!DOCTYPE html>
<html lang="en">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firebase Push Notification Test</title>
    <link href="./style.css?q=<?=time()?>" rel="stylesheet" />
</head>
<body>
    <h1>Firebase Push Notification Test</h1>
    <div id="token">Token will appear here...</div>
    <div id="logs">Logs will appear here...</div>

    <div id="notification-permission-modal" style="display: none;">
        <div class="modal-content">
            <h2>Enable Notifications</h2>
            <p>We would like to send you notifications to keep you updated.</p>
            <button id="allow-notifications">Allow Notifications</button>
            <button id="deny-notifications">Deny</button>
        </div>
    </div>
    
    <div id="overlay" class="overlay" style="display: none;"></div>
    <div id="notification-modal" class="notification-modal" style="display: none;cursor:pointer">
        <div class="modal-content">
            <img id="notification-image" src="" alt="Notification Image">
            <h3 id="notification-title">Title</h3>
            <p id="notification-body">Body</p>
            <button id="notification-close">Close</button>
        </div>
    </div>
    <script type="module" src="script.js?q=<?=time()?>"></script>
</body>
</html>
