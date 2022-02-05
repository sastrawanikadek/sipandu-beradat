$(document).ready(async () => {
  if (!localStorage.getItem("username")) {
    window.location.href = "https://sipanduberadat.com/superadmin/pages/login/"
    return
  }

  $("#nama-admin").html(localStorage.getItem("username"));
  $("#sidebar-nama-admin").html(localStorage.getItem("username"));
});
