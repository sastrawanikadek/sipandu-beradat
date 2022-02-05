$("#btn-hapus-banjar").click(async () => {
  await removeBanjar();
});

const removeBanjar = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const idBanjar = $("#hapus-id").val();
  fd.append("XAT", XAT);
  fd.append("id", idBanjar);

  const req = await fetch("https://sipanduberadat.com/api/banjar/delete/", {
    method: "POST",
    body: fd,
  });

  const { status_code, data, message } = await req.json();
  swaloading(status_code, "banjar.html", removeBanjar, message);
};
