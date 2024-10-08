<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Multiple YouTube Videos</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://www.youtube.com/iframe_api"></script> <!-- YouTube API -->
</head>
<body>
<div class="container mt-5">
    <div class=" d-flex justify-content-center mb-3">
        <button id="playAllBtn" class="btn btn-success mx-2">Play All</button>
        <button id="stopAllBtn" class="btn btn-danger mx-2">Stop All</button>
        <button id="muteAllBtn" class="btn btn-warning mx-2">Mute/Unmute All</button>
        <button id="unmuteSpecificBtn" class="btn btn-info mx-2">Unmute One Video</button>
    </div>
    
    <div class="row">
        <?php
        // Get the YouTube video URL from the GET request
        $youtube_url = isset($_GET['video']) ? $_GET['video'] : 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';

        // Extract the video ID from the YouTube URL
        parse_str(parse_url($youtube_url, PHP_URL_QUERY), $url_params);
        $video_id = isset($url_params['v']) ? $url_params['v'] : '';

        if ($video_id) {
            // Create 4 instances of the YouTube video
            for ($i = 0; $i < 4; $i++) {
                echo '<div class="col-md-6 mb-4">';
                echo '<div class="ratio ratio-16x9">';
                // Each iframe gets a unique id for controlling the playback later
                echo '<iframe class="youtube-player" id="player' . $i . '" data-loaded="false" src="https://www.youtube.com/embed/' . $video_id . '?enablejsapi=1&mute=1" title="YouTube video" allow="autoplay; encrypted-media" allowfullscreen></iframe>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p class="text-danger">Invalid YouTube URL</p>';
        }
        ?>
    </div>

</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<!-- YouTube API Script -->
<script>
    var players = []; 
    var isMuted = true; 

    // Initialize YouTube Iframe API
    function onYouTubeIframeAPIReady() {
        const iframes = document.querySelectorAll('.youtube-player');

        iframes.forEach(function(iframe, index) {
            players[index] = new YT.Player(iframe.id, {
                events: {
                    'onReady': onPlayerReady
                }
            });
        });
    }

    // When the player is ready, it will be muted by default
    function onPlayerReady(event) {
        event.target.mute();
    }
    
    // Play all videos
    document.getElementById('playAllBtn').addEventListener('click', function() {
        players.forEach(function(player) {
            player.playVideo();
        });
    });

    // Stop all videos
    document.getElementById('stopAllBtn').addEventListener('click', function() {
        players.forEach(function(player) {
            player.stopVideo();
        });
    });

    // Mute/Unmute all videos
    document.getElementById('muteAllBtn').addEventListener('click', function() {
        if (isMuted) {
            players.forEach(function(player) {
                player.unMute();
            });
            isMuted = false;
        } else {
            players.forEach(function(player) {
                player.mute();
            });
            isMuted = true;
        }
    });

    // Unmute only a specific video (for example, video with index 0)
    document.getElementById('unmuteSpecificBtn').addEventListener('click', function() {
        // Mute all first
        players.forEach(function(player) {
            player.mute();
        });

        players[0].unMute();  // Change the index if you want to unmute a different video (0 for the first, 1 for the second, etc.)
    });
</script>
</body>
</html>
