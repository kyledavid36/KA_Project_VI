import re

def apply_form_validation_to_html(file_path):
    with open(file_path, 'r', encoding='utf-8') as file:
        html = file.read()

    validation_script = """
<script>
document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector(".needs-validation");
  const username = document.getElementById("username");
  const password = document.getElementById("password");
  const reason = document.getElementById("reason");
  const charCount = document.getElementById("charCount");

  const showInvalid = (input, message) => {
    input.classList.add("is-invalid");
    input.nextElementSibling.textContent = message;
  };

  const clearInvalid = (input) => {
    input.classList.remove("is-invalid");
  };

  // Character count for textarea
  reason.addEventListener("input", () => {
    const maxLength = 180;
    const currentLength = reason.value.length;
    charCount.textContent = (maxLength - currentLength) + " characters remaining";
    if (currentLength > maxLength) {
      reason.classList.add("is-invalid");
    } else {
      reason.classList.remove("is-invalid");
    }
  });

  // Username and password blur validation
  username.addEventListener("blur", () => {
    clearInvalid(username);
    if (username.value.length < 7) {
      showInvalid(username, "Username must be at least 7 characters long.");
    }
  });

  password.addEventListener("blur", () => {
    clearInvalid(password);
    const value = password.value;
    const hasUpper = /[A-Z]/.test(value);
    const hasNumber = /[0-9]/.test(value);

    if (value.length < 7) {
      showInvalid(password, "Password must be at least 7 characters.");
    } else if (!hasUpper || !hasNumber) {
      showInvalid(password, "Password must contain an uppercase letter and a number.");
    }
  });

  form.addEventListener("submit", function (event) {
    let valid = true;

    // Custom checks
    if (username.value.length < 7) {
      showInvalid(username, "Username must be at least 7 characters long.");
      valid = false;
    }
    if (password.value.length < 7 || !/[A-Z]/.test(password.value) || !/[0-9]/.test(password.value)) {
      showInvalid(password, "Password must be at least 7 characters and contain an uppercase letter and number.");
      valid = false;
    }
    if (reason.value.length > 180) {
      reason.classList.add("is-invalid");
      valid = false;
    }

    if (!form.checkValidity() || !valid) {
      event.preventDefault();
      event.stopPropagation();
    }

    form.classList.add("was-validated");
  });
});
</script>
"""

    # Avoid duplication if the script is already there
    if "<script>" in html and "form.addEventListener(\"submit\"" in html:
        print("Validation script already present. Skipping injection.")
        return

    # Inject script before closing </body>
    modified_html = re.sub(r"</body\s*>", validation_script + "\n</body>", html, flags=re.IGNORECASE)

    with open(file_path, 'w', encoding='utf-8') as file:
        file.write(modified_html)

    print(f"Validation script added to {file_path}")

# Use the function on your file
apply_form_validation_to_html("request_access.html")
