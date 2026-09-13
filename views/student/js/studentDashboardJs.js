const logoutBtn = document.getElementById("logoutBtn");
logoutBtn.addEventListener("click", logoutMe);

function logoutMe() {
    location.href = "../logout.php";
}