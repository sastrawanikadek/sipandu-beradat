$("#btn-hapus-negara").click(async () => {
  await removeNegara();
});

const removeNegara = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const idNegaraHapus = $("#hapus-id").val();
  fd.append("XAT", XAT);
  fd.append("id", idNegaraHapus);

  const req = await fetch("https://sipanduberadat.com/api/negara/delete/", {
    method: "POST",
    body: fd,
  });

  const { status_code, message } = await req.json();
  swaloading(status_code, "negara.html", removeNegara, message);
};
