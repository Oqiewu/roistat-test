class Timer {
    constructor(elementId) {
        this.element = document.getElementById(elementId);
        this.startTime = Date.now();
        this.timerInterval = null;
    }

    formatTime(ms) {
        const seconds = Math.floor(ms / 1000);
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;
        return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    update() {
        const elapsedTime = Date.now() - this.startTime;
        this.element.textContent = `Время на сайте: ${this.formatTime(elapsedTime)}`;
    }

    start() {
        this.timerInterval = setInterval(() => this.update(), 1000);
    }

    stop() {
        clearInterval(this.timerInterval);
    }
}
