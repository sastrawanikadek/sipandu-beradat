$("#form-edit-pecalang").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await updatePecalang();
});

const updatePecalang = async () => {
  const id = $("#edit-id").val();
  const id_masyarakat = $("#edit-id-masyarakat").val();
  const active_status = $("#edit-active-status").val();
  const sirine_authority = $("#edit-sirine-authority").val();
  const status_prajuru = $("#edit-status-prajuru").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("id_masyarakat", id_masyarakat);
  fd.append("active_status", active_status);
  fd.append("sirine_authority", sirine_authority);
  fd.append("prajuru_status", status_prajuru);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/pecalang/update/", {
    method: "POST",
    body: fd,
  });
  const { status_code, message } = await req.json();

  if (status_code === 200) {
    await readPecalang();
    stopLoading();
    Swal.fire({
      title: "Proses berhasil",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 400) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan",
      text: message,
      icon: "warning",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(updatePecalang);
  }
};
