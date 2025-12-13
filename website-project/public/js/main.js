function loadHTML(filePath, elementId) {
    fetch(filePath)
        .then(response => response.text())
        .then(data => {
            document.getElementById(elementId).innerHTML = data;
        });
}

document.addEventListener("DOMContentLoaded", function() {
    loadHTML('includes/navbar.html', 'navbar-container');
    loadHTML('includes/footer.html', 'footer-container');
});