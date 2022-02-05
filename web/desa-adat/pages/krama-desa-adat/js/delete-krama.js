$(document).ready(function () {
  readStatusAktifDelete();
});

$("#btn-hapus-krama").click(async () => {
  startLoading();
  await deleteKrama();
});

const readStatusAktifDelete = async () => {
  const req = await fetch(
    `https://sipanduberadat.com/api/status-aktif-masyarakat/`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    data.map((obj) => {
      if (!obj.status) {
        const option = `<option value="${obj.id}">${obj.name}</option>`;
        $("#hapus-active-status").append(option);
      }
    });
  } else {
    readStatusAktifDelete();
  }
};

const deleteKrama = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const id = $("#hapus-id").val();
  const id_active_status = $("#hapus-active-status").val();
  fd.append("id", id);
  fd.append("active_status_id", id_active_status);
  fd.append("XAT", XAT);

  const req = await fetch("https://sipanduberadat.com/api/masyarakat/delete/", {
    method: "POST",
    body: fd,
  });

  const { status_code, message } = await req.json();

  if (status_code === 200) {
    await readKrama();
    stopLoading();
    $("#modal-hapus-krama").modal("hide");
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 400) {
    stopLoading();
    $("#modal-hapus-krama").modal("hide");
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(deleteKrama);
  }
};
