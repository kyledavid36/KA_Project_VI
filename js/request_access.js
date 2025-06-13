// ===============================
// Q25 - Character Counter Script
// For <textarea> input "reason"
// ===============================

// Global variable for the <textarea> element
var el;

// Function to count characters and display remaining/last key
function charCount(e) {
  var textEntered, charDisplay, counter, lastkey;

  // Get textarea value
  textEntered = document.getElementById('reason').value;            // text input by user

  // Get supporting display elements
  charDisplay = document.getElementById('charCount');               // element for showing characters left
  lastkey = document.getElementById('lastkey');                     // element for showing last key pressed

  // Remaining characters
  counter = 180 - textEntered.length;

  // Update character counter display
  if (counter >= 0) {
    charDisplay.innerHTML = 'Characters remaining: ' + counter;    // within limit
    charDisplay.style.color = 'black';
  } else {
    charDisplay.innerHTML = '⚠️ You have exceeded the 180 character limit!';
    charDisplay.style.color = 'red';
  }

  // Display last key pressed (convert keyCode to character)
  if (e && e.keyCode) {
    lastkey.innerHTML = 'Last key input: ' + String.fromCharCode(e.keyCode);
  }
}

// Get the textarea element and attach the event listener
el = document.getElementById('reason');                             // Get the <textarea>
el.addEventListener('keypress', charCount, false);                  // Listen for keypress and call charCount
el.addEventListener('input', charCount, false);                     // Also handle backspace/paste/etc.
