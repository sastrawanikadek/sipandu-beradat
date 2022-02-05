$(document).ready(async () => {
  readProfil();
});

const readProfil = async () => {
  startLoading();
  const fd = new FormData();
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);
  const req = await fetch(
    "https://sipanduberadat.com/api/admin-desa-adat/me/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, message, data } = await req.json();

  if (status_code === 200) {
    stopLoading();
    $("#view-cover").attr("src", data.masyarakat.avatar);
    $("#view-avatar").attr("src", data.masyarakat.avatar);
    $("#view-name").html(data.masyarakat.name);
    $("#view-desa-adat").html(data.masyarakat.banjar.desa_adat.name);
    $("#view-active-status").html(
      Number(data.active_status) === 1 ? "Aktif" : "Tidak Aktif"
    );
    $("#view-superadmin-status").html(
      Number(data.super_admin_status) === 1
        ? '<i class="mdi mdi-circle fa-xs text-primary mr-2"></i><span>Superadmin</span>'
        : '<i class="mdi mdi-circle fa-xs text-secondary mr-2"></i><span>Admin</span>'
    );

    $("#nama-admin").val(data.masyarakat.name);
    $("#nik-admin").val(data.masyarakat.nik);
    $("#gender-admin").val(
      data.masyarakat.gender === "l" ? "Laki-laki" : "Perempuan"
    );
    $("#phone-admin").val(data.masyarakat.phone);
    $("#birth-admin").val(data.masyarakat.date_of_birth);
  } else if (status_code === 401) {
    refreshToken(readProfil);
  }
};
