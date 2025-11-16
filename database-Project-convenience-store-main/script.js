document.addEventListener('DOMContentLoaded', function() {
    
    // --- Anti-Double-Submission Fix ---
    // This code prevents the user (or the browser) from submitting the "Add to Cart" 
    // form twice by disabling the submit button immediately upon the first click.

    // 1. Target the Hero Product form (Chocolate Bar) by its unique ID
    const heroForm = document.getElementById('hero-add-form');

    if (heroForm) {
        heroForm.addEventListener('submit', function(event) {
            const submitButton = heroForm.querySelector('button[type="submit"]');

            if (submitButton) {
                // Disable button and provide user feedback
                submitButton.disabled = true;
                submitButton.textContent = 'Adding...'; 
            }
        });
    }

    // 2. Target all other Popular Product forms (the '+' buttons)
    // Selects all forms that submit to ADDTOCART.php
    const popularForms = document.querySelectorAll('form[action="ADDTOCART.php"]');

    popularForms.forEach(form => {
        // Exclude the hero form since it was handled above
        if (form.id !== 'hero-add-form') {
            form.addEventListener('submit', function(event) {
                const submitButton = form.querySelector('button[type="submit"]');
    
                if (submitButton) {
                    // Disable the button instantly
                    submitButton.disabled = true;
                    // You could change the content of the button here too if needed
                }
            });
        }
    });

    // NOTE: The old renderCards() and quantity control code were removed
    // because product rendering is now done by PHP, and the old quantity
    // buttons do not match the current simplified forms.
});