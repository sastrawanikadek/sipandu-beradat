$("#btn-hapus-fasilitas").click(async () => {
  await removeFasilitas();
});

const removeFasilitas = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const idFasilitas = $("#hapus-id").val();
  fd.append("XAT", XAT);
  fd.append("id", idFasilitas);

  const req = await fetch("https://sipanduberadat.com/api/fasilitas/delete/", {
    method: "POST",
    body: fd,
  });

  const { status_code, data, message } = await req.json();
  swaloading(status_code, "fasilitas.html", removeFasilitas, message);
};
