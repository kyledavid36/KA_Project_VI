// ✅ Q25 - Character counter with warning & last key pressed
document.addEventListener("DOMContentLoaded", function () {
  const reasonTextarea = document.getElementById("reason");
  const charCount = document.getElementById("charCount");
  const maxChars = 180;

  if (reasonTextarea && charCount) {
    reasonTextarea.addEventListener("input", function (event) {
      const currentLength = reasonTextarea.value.length;
      const remaining = maxChars - currentLength;

      // Display last character typed
      const lastChar = reasonTextarea.value.slice(-1);

      if (remaining >= 0) {
        charCount.textContent = `${remaining} characters remaining. Last key: "${lastChar}"`;
        charCount.classList.remove("text-danger");
        charCount.classList.add("text-muted");
      } else {
        charCount.textContent = `❌ You have exceeded the maximum (180). Last key: "${lastChar}"`;
        charCount.classList.add("text-danger");
      }
    });
  }
});
