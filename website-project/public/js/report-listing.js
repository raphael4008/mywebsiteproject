import apiClient from './apiClient.js';

document.addEventListener('DOMContentLoaded', () => {
    const reportForm = document.getElementById('reportListingForm');
    
    if (reportForm) {
        reportForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const submitBtn = reportForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            const urlParams = new URLSearchParams(window.location.search);
            const listingId = urlParams.get('id');
            const formData = new FormData(reportForm);
            const data = Object.fromEntries(formData.entries());
            data.listing_id = listingId;

            try {
                await apiClient.request('/users/listings/report', 'POST', data);
                alert('Thank you. This listing has been flagged for review.');
                bootstrap.Modal.getInstance(document.getElementById('reportListingModal')).hide();
                reportForm.reset();
            } catch (error) {
                alert('Failed to submit report: ' + error.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    }
});