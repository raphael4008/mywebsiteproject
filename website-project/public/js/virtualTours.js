document.addEventListener('DOMContentLoaded', () => {
    const tourButtons = document.querySelectorAll('.virtual-tour-btn');

    tourButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tourUrl = button.dataset.tourUrl;

            if (tourUrl) {
                const modal = document.getElementById('virtualTourModal');
                const iframe = modal.querySelector('iframe');

                iframe.src = tourUrl;
                modal.classList.add('show');
                modal.style.display = 'block';
            }
        });
    });

    const closeModal = document.getElementById('closeTourModal');
    closeModal.addEventListener('click', () => {
        const modal = document.getElementById('virtualTourModal');
        const iframe = modal.querySelector('iframe');

        iframe.src = '';
        modal.classList.remove('show');
        modal.style.display = 'none';
    });
});