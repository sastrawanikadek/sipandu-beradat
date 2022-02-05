$("#btn-hapus-kabupaten").click(async () => {
  await removeKabupaten();
});

const removeKabupaten = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const idKabupatenHapus = $("#hapus-id").val();
  fd.append("XAT", XAT);
  fd.append("id", idKabupatenHapus);

  const req = await fetch("https://sipanduberadat.com/api/kabupaten/delete/", {
    method: "POST",
    body: fd,
  });

  const { status_code, data, message } = await req.json();

  swaloading(status_code, "kabupaten.html", removeKabupaten, message);
};
