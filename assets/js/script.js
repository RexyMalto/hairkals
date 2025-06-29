document.addEventListener("DOMContentLoaded", function () {
  const navToggler = document.querySelector('.navbar-toggler');
  const navCollapse = document.querySelector('.navbar-collapse');

  if (navToggler && navCollapse) {
    navToggler.addEventListener('click', () => {
      navCollapse.classList.toggle('show');
    });
  }
});
