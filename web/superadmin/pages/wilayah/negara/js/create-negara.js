$("#form-tambah-negara").submit(async (e) => {
  e.preventDefault();
  await addNegara();
});

const addNegara = async () => {
  startLoading();
  const name = $("#tambah-name").val();
  const icon = $("#tambah-icon").prop("files");

  const fd = new FormData();
  fd.append("name", name);
  if (icon.length > 0) {
    fd.append("flag", icon[0]);
  }
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/negara/create/", {
    method: "POST",
    body: fd,
  });
  const { status_code, message } = await req.json();
  stopLoading();
  swaloading(status_code, "negara.html", addNegara, message);
};
