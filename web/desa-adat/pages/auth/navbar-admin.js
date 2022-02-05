$(document).ready(async () => {
  $(".nama-admin").html(localStorage.getItem("name"));
  $(".avatar-admin").attr("src", localStorage.getItem("avatar"));
  $(".nama-desa").text(localStorage.getItem("desa_adat"));

  if (localStorage.getItem("super_admin_status") === "false") {
    $("#sidenav-admin").hide();
  }
});
