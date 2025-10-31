<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mollieween Scary Shots</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #000000;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        h1 {
            color: #ff6b35;
            text-align: center;
            margin-bottom: 50px;
            font-size: 3rem;
            text-shadow: 0 0 20px rgba(255, 107, 53, 0.5);
            font-weight: 700;
        }

        .gallery {
            column-count: 4;
            column-gap: 20px;
        }

        .gallery.empty {
            column-count: 1;
        }

        @media (max-width: 1200px) {
            .gallery {
                column-count: 3;
            }
        }

        @media (max-width: 800px) {
            .gallery {
                column-count: 2;
            }
        }

        @media (max-width: 500px) {
            .gallery {
                column-count: 1;
            }
        }

        .image-card {
            background: white;
            padding: 15px;
            padding-bottom: 60px;
            margin-bottom: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
            transition: transform 0.3s, box-shadow 0.3s;
            animation: slideIn 0.5s ease-out;
            break-inside: avoid;
            display: inline-block;
            width: 100%;
            position: relative;
        }

        /* Random rotation for polaroid effect */
        .image-card:nth-child(3n+1) {
            transform: rotate(-2deg);
        }

        .image-card:nth-child(3n+2) {
            transform: rotate(1.5deg);
        }

        .image-card:nth-child(3n+3) {
            transform: rotate(-1deg);
        }

        .image-card.deleting {
            animation: slideOut 0.5s ease-out forwards;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) rotate(0deg);
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideOut {
            to {
                opacity: 0;
                transform: translateY(-20px) scale(0.9) rotate(0deg);
            }
        }

        .image-card:hover {
            transform: scale(1.05) rotate(0deg) !important;
            box-shadow: 0 15px 40px rgba(255, 107, 53, 0.4);
            z-index: 10;
        }

        .image-wrapper {
            position: relative;
            width: 100%;
            background: #f0f0f0;
        }

        .image-wrapper img {
            display: block;
            width: 100%;
            height: auto;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .empty-state img {
            max-width: 300px;
            width: 100%;
            height: auto;
            opacity: 0.6;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        .connection-status {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid oklch(39.3% 0.095 152.535);
            color: oklch(72.3% 0.219 149.579);
        }

        .connection-status.disconnected {
            border: 1px solid oklch(39.6% 0.141 25.723);
            color: oklch(63.7% 0.237 25.331);
        }

        .status-indicator {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: oklch(72.3% 0.219 149.579);
        }

        .connection-status.disconnected .status-indicator {
            background: oklch(63.7% 0.237 25.331);
        }
    </style>
</head>
<body>
    <div class="connection-status">
        <span class="status-indicator" id="connectionIndicator"></span>
        <span id="connectionText">Connected</span>
    </div>

    <div class="container">
        <h1>🎃 Mollieween Scary Shots 👻</h1>

        <div class="gallery" id="gallery">
            <div class="empty-state">
                <img src="/resources/images/ghost.png" alt="No photos yet">
            </div>
        </div>
    </div>

    <script>
        const gallery = document.getElementById('gallery');
        const connectionStatus = document.querySelector('.connection-status');
        const connectionIndicator = document.getElementById('connectionIndicator');
        const connectionText = document.getElementById('connectionText');

        let eventSource = null;

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Load initial images
        async function loadImages() {
            try {
                const response = await fetch('/api/images');
                const data = await response.json();
                displayImages(data.images);
            } catch (error) {
                console.error('Error loading images:', error);
                gallery.innerHTML = '<div class="empty-state">Error loading images</div>';
            }
        }

        // Display images in gallery
        function displayImages(images) {
            if (images.length === 0) {
                gallery.classList.add('empty');
                gallery.innerHTML = '<div class="empty-state"><img src="/images/ghost.png" alt="No photos yet"></div>';
                return;
            }

            gallery.classList.remove('empty');
            gallery.innerHTML = images.map(image => createImageCard(image)).join('');
        }

        // Create image card HTML
        function createImageCard(image) {
            const date = new Date(image.created_at).toLocaleString();
            const sizeKB = (image.size / 1024).toFixed(2);
            
            return `
                <div class="image-card" data-id="${image.id}">
                    <div class="image-wrapper">
                        <img src="${image.url}" alt="${image.filename}" loading="lazy">
                    </div>
                </div>
            `;
        }

        // Delete image
        window.deleteImage = async function(id) {
            if (!confirm('Are you sure you want to delete this image?')) {
                return;
            }

            const card = document.querySelector(`[data-id="${id}"]`);
            if (card) {
                card.classList.add('deleting');
            }

            try {
                const response = await fetch(`/api/images/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (!response.ok) {
                    throw new Error('Delete failed');
                }

                // Image will be removed via SSE
            } catch (error) {
                console.error('Error deleting image:', error);
                if (card) {
                    card.classList.remove('deleting');
                }
                alert('Failed to delete image');
            }
        };

        // Set up Server-Sent Events
        function setupEventSource() {
            eventSource = new EventSource('/api/events');

            eventSource.onopen = () => {
                console.log('SSE connection established');
                connectionStatus.classList.remove('disconnected');
                connectionText.textContent = 'Connected';
            };

            eventSource.onerror = () => {
                console.error('SSE connection error');
                connectionStatus.classList.add('disconnected');
                connectionText.textContent = 'Disconnected';
                
                // Reconnect after 5 seconds
                eventSource.close();
                setTimeout(setupEventSource, 5000);
            };

            eventSource.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    
                    if (data.type === 'created') {
                        addImageToGallery(data.image);
                    } else if (data.type === 'deleted') {
                        removeImageFromGallery(data.id);
                    }
                } catch (error) {
                    // Heartbeat or other non-JSON messages
                }
            };
        }

        // Add new image to gallery
        function addImageToGallery(image) {
            const emptyState = gallery.querySelector('.empty-state');
            if (emptyState) {
                gallery.classList.remove('empty');
                gallery.innerHTML = '';
            }

            const existingCard = document.querySelector(`[data-id="${image.id}"]`);
            if (!existingCard) {
                gallery.insertAdjacentHTML('afterbegin', createImageCard(image));
            }

            // Keep only last 50 images
            const cards = gallery.querySelectorAll('.image-card');
            if (cards.length > 50) {
                cards[cards.length - 1].remove();
            }
        }

        // Remove image from gallery
        function removeImageFromGallery(id) {
            const card = document.querySelector(`[data-id="${id}"]`);
            if (card) {
                card.classList.add('deleting');
                setTimeout(() => {
                    card.remove();
                    
                    // Show empty state if no images left
                    if (gallery.children.length === 0) {
                        gallery.classList.add('empty');
                        gallery.innerHTML = '<div class="empty-state"><img src="/resources/images/ghost.png" alt="No photos yet"></div>';
                    }
                }, 500);
            }
        }

        // Initialize
        loadImages();
        setupEventSource();
    </script>
</body>
</html>
