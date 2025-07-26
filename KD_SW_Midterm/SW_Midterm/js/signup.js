// js/signup.js

document.addEventListener('DOMContentLoaded', () => {
    const signupForm = document.getElementById('signupForm');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');

    // Get feedback elements
    const usernameFeedback = document.getElementById('usernameFeedback');
    // passwordFeedback is now static text in HTML, so we just use it for class toggling
    const passwordFeedbackContainer = document.getElementById('passwordFeedback');

    /**
     * Validates the username input field.
     * Shows/hides invalid feedback based on length.
     */
    function validateUsername() {
        if (usernameInput.value.length < 7) { // Check for length, including empty string
            usernameInput.classList.add('is-invalid');
            usernameFeedback.textContent = 'Username must be at least 7 characters long.';
            return false;
        } else {
            usernameInput.classList.remove('is-invalid');
            return true;
        }
    }

    /**
     * Validates the password input field.
     * Shows/hides invalid feedback based on length and new requirements.
     */
    function validatePassword() {
        let isValid = true;
        
        // Always add is-invalid if any requirement is not met, including empty
        if (passwordInput.value.length < 7 || !/[A-Z]/.test(passwordInput.value) || !/\d/.test(passwordInput.value)) {
            passwordInput.classList.add('is-invalid');
            isValid = false;
        } else {
            passwordInput.classList.remove('is-invalid');
        }
        return isValid;
    }

    // Event listeners for blur (when user clicks off the field)
    usernameInput.addEventListener('blur', validateUsername);
    passwordInput.addEventListener('blur', validatePassword);

    // Event listeners for input (real-time visual feedback for password requirements)
    usernameInput.addEventListener('input', validateUsername);
    passwordInput.addEventListener('input', validatePassword);


    signupForm.addEventListener('submit', function(event) {
        // Perform final validation check on submit for all fields
        const isUsernameValid = validateUsername();
        const isPasswordValid = validatePassword();

        if (!isUsernameValid || !isPasswordValid) {
            event.preventDefault(); // Prevent form submission if validation fails
        } else {
            // Form is valid, proceed with submission (or AJAX call)
            // For a real application, form submission would handle the backend registration.
            // console.log("Form is valid! Submitting...");
            // alert("Sign Up Successful! (This is a demo, form would submit to backend)");
            // event.preventDefault(); // Uncomment if you want to prevent actual submission for testing
            // window.location.href = "success.html"; // Example redirect
        }
    });
});
