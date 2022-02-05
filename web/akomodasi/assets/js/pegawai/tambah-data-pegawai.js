$("#form-tambah-pegawai").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await tambahPegawai();
});

const tambahPegawai = async () => {
  const id_akomodasi = localStorage.getItem("id_akomodasi");
  const id_negara = $("#tambah-negara").val();
  const name = $("#tambah-name").val();
  const phone = $("#tambah-phone").val();
  const date_of_birth = $("#tambah-tanggal-lahir").val();
  const nik = $("#tambah-nik").val();
  const gender = $("#tambah-gender").val();
  const avatar = $("#tambah-avatar").prop("files");

  const fd = new FormData();
  fd.append("id_akomodasi", id_akomodasi);
  fd.append("id_negara", id_negara);
  fd.append("name", name);
  fd.append("phone", phone);
  fd.append("date_of_birth", date_of_birth);
  fd.append("nik", nik);
  fd.append("gender", gender);
  if (avatar.length > 0) {
    fd.append("avatar", avatar[0]);
  }
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/pegawai-akomodasi/create/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, message, data } = await req.json();

  if (status_code === 200) {
    await read_pegawai();
    stopLoading();
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 400) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(tambahPegawai);
  }
};
