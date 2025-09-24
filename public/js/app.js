// app.js
(function () {
  const sidebar = document.querySelector(".sidebar-toggle-btn");
  const sidebarEl = document.querySelector("#sidebar");
  const navRoot = document.querySelector(".sidebar-nav");
  const BODY = document.body;
  const STORAGE_KEY = "daijo.sidebar.open";
  const mqlDesktop = window.matchMedia("(pointer:fine)");
  let isExpand = localStorage.getItem(STORAGE_KEY) === "1";

  function setExpand(expand) {
    isExpand = !!expand;
    sidebarEl.classList.toggle("expand", isExpand);
    BODY.classList.toggle("sidebar-open", isExpand);
    BODY.classList.toggle("sidebar-closed", !isExpand);
    localStorage.setItem(STORAGE_KEY, isExpand ? "1" : "0");
    // Update aria on toggle button
    if (sidebar) sidebar.setAttribute("aria-expanded", String(isExpand));
  }

  function toggleSidebar() {
    setExpand(!isExpand);
  }

  // init
  setExpand(isExpand);

  // click toggle
  sidebar?.addEventListener("click", toggleSidebar);

  // Optional: hover-to-expand only on desktop pointers
  let hoverLock = false;
  const deb = (fn, d = 100) => {
    let t;
    return (...a) => {
      clearTimeout(t);
      t = setTimeout(() => fn(...a), d);
    };
  };

  if (mqlDesktop.matches) {
    navRoot?.addEventListener("mouseenter", () => {
      if (!isExpand && !hoverLock) {
        setExpand(true);
        hoverLock = true;
      }
    });
    navRoot?.addEventListener(
      "mouseleave",
      deb(() => {
        if (hoverLock) {
          setExpand(false);
          hoverLock = false;
        }
      }, 150),
    );
  }

  // Close all collapses when narrowing viewport if needed
  window.addEventListener(
    "resize",
    deb(() => {
      // You can add responsive behaviors here if desired
    }, 150),
  );
})();
