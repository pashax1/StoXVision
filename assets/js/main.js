const stocks = ["RELIANCE", "TCS", "INFY", "HDFCBANK", "SBIN", "ITC", "LT", "ICICIBANK"];

const container = document.querySelector(".floating-container");

if(container){
    setInterval(() => {
        const span = document.createElement("span");
        span.innerText = stocks[Math.floor(Math.random() * stocks.length)];
        span.style.left = Math.random() * 100 + "vw";
        span.style.fontSize = (Math.random() * 20 + 15) + "px";
        span.style.animationDuration = (Math.random() * 5 + 5) + "s";
        container.appendChild(span);

        setTimeout(() => {
            span.remove();
        }, 10000);
    }, 800);
}