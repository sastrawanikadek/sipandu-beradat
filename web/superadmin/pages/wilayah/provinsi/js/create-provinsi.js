$("#form-tambah-provinsi").submit(async (e) => {
  e.preventDefault();
  await addProvinsi();
});

const addProvinsi = async () => {
  startLoading();
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const id_negara = $("#tambah-negara").val();
  const name = $("#tambah-provinsi").val();

  const fd = new FormData();
  fd.append("id_negara", id_negara);
  fd.append("name", name);
  fd.append("XAT", XAT);

  const req = await fetch("https://sipanduberadat.com/api/provinsi/create/", {
    method: "POST",
    body: fd,
  });
  const { status_code, data, message } = await req.json();
  stopLoading();
  swaloading(status_code, "provinsi.html", addProvinsi, message);
};
