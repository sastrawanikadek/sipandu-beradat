$("#form-edit-profil-akomodasi").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await updateAkomodasi();
});

const updateFasilitas = async () => {
  const facilities = [];
  $("input[name='fasilitas']:checked").each(function () {
    facilities.push(this.value);
  });
  const id = $("#edit-id").val();

  const fd = new FormData();
  fd.append("facilities", JSON.stringify(facilities));

  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);
  fd.append("id_akomodasi", id);

  const req = await fetch(
    "https://sipanduberadat.com/api/fasilitas-akomodasi/update/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    await readAkomodasi();
    await getMe();
    init();
    stopLoading();
    Swal.fire({
      title: "Proses berhasil",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 400) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan",
      text: message,
      icon: "warning",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(updateFasilitas);
  }
};

const updateAkomodasi = async () => {
  const id = $("#edit-id").val();
  const name = $("#edit-name").val();
  const logo = $("#edit-logo").prop("files");
  const desc = $("#edit-desc").val();
  const cover = $("#edit-cover").prop("files");
  const lokasi = $("#edit-address").val();
  const id_provinsi = $("#edit-provinsi").val();
  const id_kabupaten = $("#edit-kabupaten").val();
  const id_kecamatan = $("#edit-kecamatan").val();
  const id_desa = $("#edit-desa-adat").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("name", name);
  if (logo.length > 0) {
    fd.append("logo", logo[0]);
  }
  fd.append("description", desc);
  if (cover.length > 0) {
    fd.append("cover", cover[0]);
  }
  fd.append("location", lokasi);
  fd.append("id-provinsi", id_provinsi);
  fd.append("id_kabupaten", id_kabupaten);
  fd.append("id_kecamatan", id_kecamatan);
  fd.append("id_desa", id_desa);
  fd.append("active_status", "true");

  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/akomodasi/update/", {
    method: "POST",
    body: fd,
  });
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    await updateFasilitas();
    await getMe();
    init();
    stopLoading();
    Swal.fire({
      title: "Proses berhasil",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    }).then((result) => {
      if (result.isConfirmed) {
        // $('#modalEditAkomodasi').modal().hide();
        $("#modalEditAkomodasi .close").click();
      }
    });
  } else if (status_code === 400) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan",
      text: message,
      icon: "warning",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(updateAkomodasi);
  }
};
