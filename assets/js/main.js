const stocks = [
    "RELIANCE", "TCS", "INFY", "HDFCBANK", "SBIN", "ITC",
    "LT", "ICICIBANK", "BHARTIARTL", "AXISBANK", "KOTAKBANK",
    "ADANIENT", "ASIANPAINT", "MARUTI", "SUNPHARMA", "TITAN"
];

const container = document.querySelector(".floating-container");

if (container) {
    function createFloatingStock() {
        const span = document.createElement("span");

        // Pick a random stock symbol
        span.innerText = stocks[Math.floor(Math.random() * stocks.length)];

        // Random horizontal position (0-95vw)
        span.style.left = Math.random() * 95 + "vw";

        // Random size (within a range for aesthetics)
        const size = (Math.random() * 0.5 + 0.8) + "rem";
        span.style.fontSize = size;

        // Random animation duration (10-25s for varied speed)
        const duration = (Math.random() * 10 + 10) + "s";
        span.style.animationDuration = duration;

        // Random initial delay
        span.style.animationDelay = (Math.random() * 5) + "s";

        container.appendChild(span);

        // Remove from DOM after animation completes to avoid memory leak
        // Duration in ms + extra buffer
        const timeToLive = (parseFloat(duration) + 1) * 1000;
        setTimeout(() => {
            span.remove();
        }, timeToLive);
    }

    // Initial batch
    for (let i = 0; i < 8; i++) {
        setTimeout(createFloatingStock, i * 1500);
    }

    // Keep creating new ones
    setInterval(createFloatingStock, 2500);
}