class FormHandler {
    constructor(formId, timer) {
        this.form = document.getElementById(formId);
        this.timer = timer;
        this.form.addEventListener('submit', (event) => this.handleSubmit(event));
    }

    handleSubmit(event) {
        event.preventDefault();

        const formData = new FormData(this.form);
        const timeSpent = (Date.now() - this.timer.startTime) >= 30000 ? 1 : 0;

        formData.append('time_spent', timeSpent);

        fetch('http://localhost:8000/index.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            this.form.reset();
        })
    }
}
