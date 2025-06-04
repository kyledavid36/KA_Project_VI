// When the page fully loads
window.addEventListener("load", function () {
  // Focus on the username input
  var usernameInput = document.getElementById("username");
  usernameInput.focus();
});

// Wait until the DOM is fully loaded
document.addEventListener("DOMContentLoaded", function () {
  // Get form and inputs
  var form = document.querySelector("form");
  var usernameInput = document.getElementById("username");
  var passwordInput = document.getElementById("password");

  // Add submit event listener to the form
  form.addEventListener("submit", function (event) {
    // Check if username and password are at least 7 characters
    if (usernameInput.value.length < 7 || passwordInput.value.length < 7) {
      alert("Username and Password must be at least 7 characters.");
      event.preventDefault(); // Stop the form from submitting
    }
  });
});
