// ✅ Q24 - Focus on Username input when page fully loads
window.addEventListener("load", function () {
  var usernameInput = document.getElementById("username");
  if (usernameInput) {
    usernameInput.focus(); // Give focus on load
  }
});

// ✅ Q20/Q23 - Validate Username & Password on form submission
document.addEventListener("DOMContentLoaded", function () {
  var form = document.querySelector("form");
  var usernameInput = document.getElementById("username");
  var passwordInput = document.getElementById("password");
  var feedbackBox = document.getElementById("feedback");

  if (form && usernameInput && passwordInput) {
    form.addEventListener("submit", function (event) {
      let errors = [];

      // Check length
      if (usernameInput.value.length < 7 || passwordInput.value.length < 7) {
        errors.push("⚠️ Username and Password must be at least 7 characters.");
      }

      // Check password for uppercase and number
      const hasUpper = /[A-Z]/.test(passwordInput.value);
      const hasNumber = /[0-9]/.test(passwordInput.value);

      if (!hasUpper || !hasNumber) {
        errors.push("⚠️ Password must contain at least one uppercase letter and one number.");
      }

      if (errors.length > 0) {
        event.preventDefault(); // Stop submission
        if (feedbackBox) {
          feedbackBox.style.color = "red";
          feedbackBox.style.fontSize = "0.95rem";
          feedbackBox.style.marginTop = "10px";
          feedbackBox.innerHTML = errors.map(e => `<p>${e}</p>`).join("");
        } else {
          alert(errors.join("\n")); // fallback if feedback div missing
        }
      }
    });
  }
});
