$("#form-tambah-banjar").submit(async (e) => {
  e.preventDefault();
  await addBanjar();
});

const addBanjar = async () => {
  startLoading();
  const name = $("#tambah-banjar").val();
  const idDesa = $("#tambah-desa-adat").val();

  const fd = new FormData();
  fd.append("id_desa", idDesa);
  fd.append("name", name);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/banjar/create/", {
    method: "POST",
    body: fd,
  });
  const { status_code, message, data } = await req.json();
  stopLoading();
  swaloading(status_code, "banjar.html", addBanjar, message);
};
