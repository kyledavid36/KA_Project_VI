// JavaScript to display current date and time in the homepage
var today = new Date();
var dateString = today.toDateString();  // e.g., "Tue June 3rd 2025"
var timeString = today.toLocaleTimeString(); // e.g., "3:14:52 PM"

var dateTimeElem = document.getElementById('currentDateTime');
dateTimeElem.innerHTML = "<strong>Current Date and Time:</strong> " + dateString + " " + timeString;
