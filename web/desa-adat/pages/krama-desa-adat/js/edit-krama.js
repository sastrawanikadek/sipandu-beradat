$(document).ready(function () {
  $("#edit-active-status").change((e) => {
    if (e.currentTarget.value === "false") {
      $("#edit-active-status-wrapper").show();
    } else {
      $("#edit-active-status-wrapper").hide();
    }
  });
});

$("#form-edit-krama").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await updateKrama();
});

const updateKrama = async () => {
  const id = $("#edit-id").val();
  const id_banjar = $("#edit-id-banjar").val();
  const active_status = $("#edit-active-status").val();
  const name = $("#edit-name").val();
  const phone = $("#edit-phone").val();
  const birth = $("#edit-birth").val();
  const nik = $("#edit-nik").val();
  const gender = $("#edit-gender").val();
  const category = $("#edit-categories").val();
  const valid_status = $("#edit-valid-status").val();
  const avatar = $("#edit-avatar").prop("files");
  const email = $("#edit-email").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("id_banjar", id_banjar);
  fd.append(
    "active_status",
    JSON.parse(active_status) ? 1 : $("#edit-active-status-name").val()
  );
  fd.append("phone", phone);
  fd.append("date_of_birth", birth);
  fd.append("nik", nik);
  fd.append("name", name);
  fd.append("gender", gender);
  fd.append("email", email);
  fd.append("category", category);

  fd.append("valid_status", JSON.parse(valid_status));
  if (avatar.length > 0) {
    fd.append("avatar", avatar[0]);
  }
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/masyarakat/update/", {
    method: "POST",
    body: fd,
  });
  const { status_code, message } = await req.json();

  if (status_code === 200) {
    await readKrama();
    stopLoading();
    $("#modal-edit-krama").modal("hide");
    Swal.fire({
      title: "Proses berhasil",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 400) {
    stopLoading();
    $("#modal-edit-krama").modal("hide");
    Swal.fire({
      title: "Terjadi Kesalahan",
      text: message,
      icon: "warning",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(updateKrama);
  }
};
