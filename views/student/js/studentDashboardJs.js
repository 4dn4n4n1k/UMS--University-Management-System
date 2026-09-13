const logoutBtn = document.getElementById("logoutBtn");

if (logoutBtn) {
    logoutBtn.addEventListener("click", logoutMe);
}

function logoutMe() {
    location.href = "../../models/logout.php";
}