document.addEventListener("DOMContentLoaded", function () {
  const reasonTextarea = document.getElementById("reason");
  const charCount = document.getElementById("charCount");
  const maxChars = 180;

  reasonTextarea.addEventListener("input", function () {
    const currentLength = reasonTextarea.value.length;
    const remaining = maxChars - currentLength;

    if (remaining >= 0) {
      charCount.textContent = remaining + " characters remaining";
      charCount.classList.remove("text-danger");
    } else {
      charCount.textContent = "You have exceeded the maximum number of characters!";
      charCount.classList.add("text-danger");
    }
  });
});
