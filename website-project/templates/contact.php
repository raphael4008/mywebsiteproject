<header class="bg-primary text-white py-5 text-center"
    style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('{{ basePath }}/images/b.jpg') center/cover;">
    <div class="container">
        <h1 class="display-4 fw-bold">Get in Touch</h1>
        <p class="lead">We'd love to hear from you. Please fill out the form below.</p>
    </div>
</header>

<div class="container py-5">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form id="contactForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Send Message</button>
                    </form>
                    <div id="form-feedback" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ basePath }}/js/auth.js" type="module"></script>
<script type="module">
    import apiClient from '{{ basePath }}/js/apiClient.js';
    import { showNotification } from '{{ basePath }}/js/utils.js';

    document.getElementById('contactForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const form = e.target;
        const button = form.querySelector('button[type="submit"]');
        const feedbackDiv = document.getElementById('form-feedback');

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        button.disabled = true;
        button.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"></div> Sending...';
        feedbackDiv.innerHTML = '';

        try {
            const response = await apiClient.request('/contact', 'POST', data);
            showNotification(response.message, 'success');
            form.reset();
        } catch (error) {
            const errorMessage = error.data && error.data.message ? error.data.message : 'An unexpected error occurred.';
            showNotification(errorMessage, 'error');
            if (error.data && error.data.errors) {
                Object.entries(error.data.errors).forEach(([key, value]) => {
                    const errorEl = document.createElement('div');
                    errorEl.className = 'text-danger small';
                    errorEl.textContent = value;
                    const inputEl = form.querySelector(`[name=${key}]`);
                    inputEl.parentNode.appendChild(errorEl);
                });
            }
        } finally {
            button.disabled = false;
            button.innerHTML = 'Send Message';
        }
    });
</script>