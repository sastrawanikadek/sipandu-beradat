$("#form-tambah-pengaturan").submit(async (e) => {
  e.preventDefault();
  await addPengaturan();
});

const addPengaturan = async () => {
  startLoading();
  const max_invalid_report = $("#tambah-max-report-status").val();

  const fd = new FormData();
  fd.append("max_invalid_report", max_invalid_report);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/pengaturan/create/", {
    method: "POST",
    body: fd,
  });
  const { status_code, data, message } = await req.json();
  stopLoading();

  swaloading(
    status_code,
    "pengaturan.html",
    addPengaturan,

    message
  );
};
