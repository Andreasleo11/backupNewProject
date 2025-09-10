const sidebar = document.querySelector(".sidebar-toggle-btn");
const sidebarNav = document.querySelector(".sidebar-nav");
let isExpand = false;

function toggleSidebar() {
  isExpand = !isExpand;
  document.querySelector("#sidebar").classList.toggle("expand");
}

sidebar.addEventListener("click", toggleSidebar);

sidebarNav.addEventListener("mouseover", function () {
  if (isExpand === false) {
    document.querySelector("#sidebar").classList.toggle("expand");
  }
});
sidebarNav.addEventListener("mouseout", function () {
  if (isExpand === false) {
    document.querySelector("#sidebar").classList.toggle("expand");
  }
});
