/**
 * JavaScript for About page: dynamic age and copyright.
 *Calculates age for Alan and Kyle, and updates copyright year dynamically.
 * Uses Date object
 */

// Create a Date object for today
var today = new Date();
var year = today.getFullYear();

// Alan's birth date
var alanBirthdate = new Date('July 2, 2003 00:00:00');
var alanAge = Math.floor((today.getTime() - alanBirthdate.getTime()) / 31556900000);

// Kyle's birth date (update as needed!)
var kyleBirthdate = new Date('June 10, 2000 00:00:00');
var kyleAge = Math.floor((today.getTime() - kyleBirthdate.getTime()) / 31556900000);

// Display Alan's age
document.getElementById('alan-age').textContent = "Age: " + alanAge + " years old";

// Display Kyle's age
document.getElementById('kyle-age').textContent = "Age: " + kyleAge + " years old";

// Dynamic copyright
var footElem = document.getElementById('foot');
footElem.innerHTML =
  '<p>&copy; ' + year + ' Alan Hosseinpourmoghadam & Kyle Dick. All rights reserved.</p>';
