var modal = document.getElementById("loginModal");
var btn = document.getElementById("loginBtn");
var span = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
btn.onclick = function() {
    modal.style.display = "block";
    showLogin(); // Reset to login view on open
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

// Function to show email or phone content in login form
function showContent(type) {
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    document.querySelectorAll('.content').forEach(content => content.classList.remove('active'));

    document.getElementById(`tab-${type}`).classList.add('active');
    document.getElementById(`content-${type}`).classList.add('active');
}

// Function to show registration form
function showRegistration() {
    document.querySelector('h2').innerText = 'Register';
    document.querySelectorAll('.content').forEach(content => content.classList.remove('active'));
    document.getElementById('content-register').classList.add('active');
}

// Function to show login form
function showLogin() {
    document.querySelector('h2').innerText = 'Login';
    document.querySelectorAll('.content').forEach(content => content.classList.remove('active'));
    document.getElementById('content-email').classList.add('active'); // Default to email login
    document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
    document.getElementById('tab-email').classList.add('active');
}




// Message for addition or removal of products from cart