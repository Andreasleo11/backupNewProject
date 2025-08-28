document.addEventListener("DOMContentLoaded", function () {
  const toggleFiltersButton = document.getElementById("toggleFilters");
  const filterSection = document.getElementById("filterSection");

  // Check local storage to determine if the filter section should be visible
  const showFilters = localStorage.getItem("showFilters") === "true";

  // Initialize filter section visibility based on local storage
  if (showFilters) {
    filterSection.classList.remove("d-none");
    toggleFiltersButton.innerHTML =
      "<i class='bx bxs-filter-alt' style='color:#ffffff'></i> Hide Filters";
  } else {
    filterSection.classList.add("d-none");
    toggleFiltersButton.innerHTML = "<i class='bx bx-filter-alt'></i> Show Filters";
  }

  // Toggle filter section visibility
  toggleFiltersButton?.addEventListener("click", function () {
    const isHidden = filterSection.classList.contains("d-none");
    if (isHidden) {
      filterSection.classList.remove("d-none");
      this.innerHTML = "<i class='bx bxs-filter-alt' style='color:#ffffff'></i> Hide Filters";
      localStorage.setItem("showFilters", "true"); // Remember to show filters next time
    } else {
      filterSection.classList.add("d-none");
      this.innerHTML = "<i class='bx bx-filter-alt'></i> Show Filters";
      localStorage.setItem("showFilters", "false"); // Remember to hide filters next time
    }
  });

  // Reset filters button
  document.getElementById("resetFilters")?.addEventListener("click", function () {
    const url = new URL(window.location.href);
    url.searchParams.delete("filterColumn");
    url.searchParams.delete("filterAction");
    url.searchParams.delete("filterValue");
    window.location.href = url.toString();
  });

  // Items per page change event
  document.getElementById("itemsPerPage")?.addEventListener("change", function () {
    const url = new URL(window.location.href);
    url.searchParams.set("itemsPerPage", this.value);
    window.location.href = url.toString();
  });

  // Filter input
  document.getElementById("filter-all")?.addEventListener("keyup", function () {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll("#inventory-table tr");
    rows.forEach(function (row) {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(query) ? "" : "none";
    });
  });
});
