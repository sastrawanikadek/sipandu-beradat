$("#btn-hapus-kecamatan").click(async () => {
  await removeKecamatan();
});

const removeKecamatan = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const idKecamatanHapus = $("#hapus-id").val();
  fd.append("XAT", XAT);
  fd.append("id", idKecamatanHapus);

  const req = await fetch("https://sipanduberadat.com/api/kecamatan/delete/", {
    method: "POST",
    body: fd,
  });

  const { status_code, data, message } = await req.json();
  swaloading(status_code, "kecamatan.html", removeKecamatan, message);
};
