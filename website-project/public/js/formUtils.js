export function clearErrors(fields, authMessage) {
    fields.forEach(field => {
        const errorElement = document.getElementById(`${field}-error`);
        if (errorElement) {
            errorElement.textContent = '';
        }
    });
    if (authMessage) {
        authMessage.textContent = '';
    }
}

export function showError(field, message) {
    let errorElement = document.getElementById(`${field}-error`);
    if (!errorElement) {
        errorElement = document.createElement('div');
        errorElement.id = `${field}-error`;
        errorElement.className = 'text-danger';
        const inputElement = document.getElementById(field);
        inputElement.parentNode.insertBefore(errorElement, inputElement.nextSibling);
    }
    errorElement.textContent = message;
}
