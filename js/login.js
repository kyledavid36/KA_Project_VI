// ============ Q24: Auto-focus Username Field on Page Load ============
// Function to give focus to the username field after the page loads
function setup() {
  var textInput = document.getElementById('username'); // Get the username input
  if (textInput) {
    textInput.focus(); // Place cursor in the username field
  }
}

// Hook the setup() function to the window's load event
window.addEventListener('load', setup, false);

// ============ Q23: Validate Username & Password Fields ============

var elUsername = document.getElementById('username');
var elPassword = document.getElementById('password');
var elMsg = document.getElementById('feedback'); // Error message display box

// Function to validate input fields on login form
function validateLogin(minLength) {
  var userVal = elUsername.value;
  var passVal = elPassword.value;
  var errors = [];

  // Style the error feedback box
  elMsg.style.color = 'red';
  elMsg.style.fontSize = '0.95rem';
  elMsg.style.marginTop = '10px';

  // Rule 1: Minimum character length for both fields
  if (userVal.length < minLength) {
    errors.push(`⚠️ Username must be at least <strong>${minLength}</strong> characters.`);
  }

  if (passVal.length < minLength) {
    errors.push(`⚠️ Password must be at least <strong>${minLength}</strong> characters.`);
  }

  // Rule 2: Password must include one uppercase letter and one number
  if (!/[A-Z]/.test(passVal)) {
    errors.push("⚠️ Password must contain at least <strong>one uppercase letter</strong>.");
  }

  if (!/[0-9]/.test(passVal)) {
    errors.push("⚠️ Password must contain at least <strong>one number</strong>.");
  }

  // If any errors were found, display them and block form submission
  if (errors.length > 0) {
    elMsg.innerHTML = errors.map(msg => `<p>${msg}</p>`).join('');
    return false;
  }

  // If no errors, clear the feedback area
  elMsg.innerHTML = '';
  return true;
}

// Attach the validation to the form’s submit event
document.addEventListener("DOMContentLoaded", function () {
  var form = document.querySelector("form");

  form.addEventListener("submit", function (event) {
    if (!validateLogin(7)) {
      event.preventDefault(); // Prevent submission if validation fails
    }
  });
});
