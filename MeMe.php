<?php
include("header.php");
?>

<body>
    <style>
        @keyframes slideLeft {
            from {
                transform: translateX(100vw);
            }

            to {
                transform: translateX(-100vw);
            }
        }
    </style>

    <div style="display: flex; justify-content: center; margin-top: 20px;">
        <button id="spawnBtn" style="
            padding: 15px 30px; 
            font-size: 1.2rem; 
            background-color: #5d3185ff; 
            color: white; 
            border: none; 
            border-radius: 50px; 
            cursor: pointer; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            transition: transform 0.1s;
        ">
            Kuru Kuru! (0)
        </button>
    </div>

    <script>
        let activeCount = 0;
        const btn = document.getElementById('spawnBtn');
        let burstInterval;
        let holdTimeout;

        function updateButton() {
            btn.textContent = `Kuru Kuru! (${activeCount})`;
        }

        function spawnGif() {
            activeCount++;
            updateButton();

            const img = document.createElement('img');
            img.src = './uploads/avatars/kurukuru-kururing.gif';
            img.style.position = 'fixed';

            // Random size variation between 100px and 300px
            const size = Math.floor(Math.random() * 500) + 200;
            img.style.width = size + 'px';
            img.style.height = 'auto';

            // Random vertical position
            const maxY = window.innerHeight - size;
            const top = Math.random() * maxY;

            img.style.top = top + 'px';
            img.style.left = '0';
            img.style.zIndex = '1000';
            img.style.pointerEvents = 'none';
            // Speed adjustment
            // Random speed between 2s and 5s
            const speed = Math.random() * 4 + 3;
            img.style.animation = `slideLeft ${speed}s linear forwards`;

            document.body.appendChild(img);

            // Cleanup when animation ends
            img.addEventListener('animationend', () => {
                img.remove();
                activeCount--;
                updateButton();
            });
        }

        function startBurst() {
            spawnGif(); // Spawn one immediately
            burstInterval = setInterval(spawnGif, 50); // Burst every 50ms
            btn.style.transform = 'scale(0.95)';
        }

        function stopBurst() {
            clearInterval(burstInterval);
            clearTimeout(holdTimeout);
            btn.style.transform = 'scale(1)';
        }

        // Mouse events
        btn.addEventListener('mousedown', () => {
            holdTimeout = setTimeout(startBurst, 200); // 200ms hold to start burst
        });

        btn.addEventListener('mouseup', stopBurst);
        btn.addEventListener('mouseleave', stopBurst);

        // Touch events for mobile
        btn.addEventListener('touchstart', (e) => {
            e.preventDefault(); // Prevent ghost clicks
            holdTimeout = setTimeout(startBurst, 200);
        });

        btn.addEventListener('touchend', stopBurst);

        // Click event for single taps (if not held long enough to burst)
        btn.addEventListener('click', () => {
            spawnGif();
        });
    </script>
</body>
<?php
include("footer.php");
?>