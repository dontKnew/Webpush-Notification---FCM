<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Notification</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center text-danger">Send Notification</h2>
    <div id="response" class="mt-3"></div>
    <form id="notificationForm">
        <div class="mb-3">
            <?php 
            $tokens = json_decode(file_get_contents("../tokens.json"), true);
            usort($tokens, function ($a, $b) {
                return strtotime($b['updated_at']) - strtotime($a['updated_at']);
            });
            // foreach($tokens as $d){
            //     echo $d['token']. "<br>";
            // }
            ?>
            <label for="deviceToken" class="form-label">User Devices</label>
            <select class="form-control" id="deviceToken" name="deviceToken[]" multiple required>
                <?php
                // Populate the select options with device tokens
                if (is_array($tokens)) {
                    foreach ($tokens as $token) {
                        echo "<option value=\"" . htmlspecialchars($token['token']) . "\">".htmlspecialchars($token['ip_address'])." - ".htmlspecialchars($token['device_name']) . " - " . htmlspecialchars($token['country']).",".htmlspecialchars($token['city']) . " at " . htmlspecialchars($token['updated_at']) . "</option>";
                    }
                }
                ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Notification Title</label>
            <input type="text" class="form-control" value="How to Index Website using Google API with Tool – Step by step guide practicle" id="title" name="title" required>
        </div>
        <div class="mb-3">
            <label for="body" class="form-label">Notification Body</label>
            <textarea class="form-control" id="body"name="body" rows="3" required>Don’t forget to like, subscribe, and share the video if you find it helpful!</textarea>
        </div>
        <div class="mb-3">
            <label for="image_link" class="form-label">Image Link</label>
            <input type="url" class="form-control" value="https://phpmaster.in/wp-content/uploads/2024/09/Screenshot-2024-09-24-091104.png" id="image_link" name="image_link">
        </div>
        <div class="mb-3">
            <label for="click_action" class="form-label">Click Action</label>
            <input type="url" class="form-control" value="https://phpmaster.in/how-to-index-website-using-google-api-with-tool-step-by-step-guide-practicle/" id="click_action" name="click_action">
        </div>
        <button type="submit" id="submit-button" class="btn btn-primary">Send Notification</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('notificationForm').addEventListener('submit', function(event) {
        event.preventDefault();

    
        const deviceTokens = Array.from(document.getElementById('deviceToken').selectedOptions).map(option => option.value);
        const title = document.getElementById('title').value.trim();
        const body = document.getElementById('body').value.trim();
        const image_link = document.getElementById('image_link').value.trim();
        const click_action = document.getElementById('click_action').value.trim();
        const button = document.getElementById('submit-button');
    
        button.disabled = true;
        button.innerText = "Sending...";

        fetch('./send_notification.php', {  
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ device_tokens: deviceTokens, title: title, body: body, click_action:click_action, image_link:image_link }), // Send an array of tokens
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            // Check for specific error codes and handle accordingly
            if (data.error) {
                throw new Error(data.error);
            }
            
            document.getElementById('response').innerHTML = `
              <div class="alert alert-info py-2" style="border-radius: 5px; font-family: 'Courier New', Courier, monospace; white-space: pre-wrap;">
                <strong>Response:</strong>
                <pre>${JSON.stringify(data, null, 2)}</pre>
              </div>
            `;

        })
        .catch((error) => {
            console.error('Error:', error);
            document.getElementById('response').innerHTML = `<div class="alert py-1  alert-danger">Failed to send notification: ${error.message}</div>`;
        })
        .finally(() => {
            button.disabled = false;
            button.innerText = "Send Notification";
        });
    });
</script>
</body>
</html>
