$("#form-edit-provinsi").submit(async (e) => {
  e.preventDefault();
  await updateProvinsi();
});

const updateProvinsi = async () => {
  startLoading();
  const idProvinsi = $("#edit-id").val();
  const namaNegara = $("#edit-negara").val();
  const namaProvinsi = $("#edit-provinsi").val();
  const statusProvinsi = $("#edit-active-status").val();

  const fd = new FormData();
  fd.append("id", idProvinsi);
  fd.append("id_negara", namaNegara);
  fd.append("name", namaProvinsi);
  fd.append("active_status", JSON.parse(statusProvinsi));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/provinsi/update/", {
    method: "POST",
    body: fd,
  });
  const { status_code, data, message } = await req.json();
  stopLoading();
  swaloading(status_code, "provinsi.html", updateProvinsi, message);
};
