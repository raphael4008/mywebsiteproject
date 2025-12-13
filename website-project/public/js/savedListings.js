document.addEventListener('DOMContentLoaded', () => {
    const saveButtons = document.querySelectorAll('.save-listing-btn');

    saveButtons.forEach(button => {
        button.addEventListener('click', () => {
            const listingId = button.dataset.listingId;

            if (listingId) {
                let savedListings = JSON.parse(localStorage.getItem('savedListings')) || [];

                if (savedListings.includes(listingId)) {
                    savedListings = savedListings.filter(id => id !== listingId);
                    button.textContent = 'Save Listing';
                } else {
                    savedListings.push(listingId);
                    button.textContent = 'Unsave Listing';
                }

                localStorage.setItem('savedListings', JSON.stringify(savedListings));
            }
        });
    });

    // Highlight saved listings on page load
    const savedListings = JSON.parse(localStorage.getItem('savedListings')) || [];
    saveButtons.forEach(button => {
        if (savedListings.includes(button.dataset.listingId)) {
            button.textContent = 'Unsave Listing';
        }
    });
});