$(document).ready(async () => {
  readAdmin();
  readBanjar();
});

const readBanjar = async () => {
  const idDesa = localStorage.getItem("id_desa")
  const req = await fetch(`https://api-sipandu-beradat.000webhostapp.com/banjar/?id_desa=${idDesa}`);
  const {
    status_code,
    data
  } = await req.json();

  if (status_code === 200) {
    data.map(obj => {
      const option = `<option value="${obj.id}">${obj.name}</option>`;
      $("#edit-id-banjar").append(option)
    })
  } else {
    readBanjar()
  }
}

const readAdmin = async () => {
  const fd = new FormData();
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);
  const req = await fetch("https://api-sipandu-beradat.000webhostapp.com/admin-desa-adat/me/", {
    method: "POST",
    body: fd
  });
  const {
    status_code,
    message,
    data
  } = await req.json();

  if (status_code === 200) {
    $("#admin-desa-name").html(data.masyarakat.banjar.desa_adat.name)
    $("#admin-name").html(data.masyarakat.name)
    $("#admin-username").html(data.masyarakat.username)
    $("#admin-avatar").attr("src", data.masyarakat.avatar)

    // profile
    $("#admin-phone").val(data.masyarakat.phone)
    $("#admin-gender").val(data.masyarakat.gender)
    $("#admin-category").val(data.masyarakat.category)
    $("#admin-category").val(data.masyarakat.category)
    $("#admin-birthday").val(data.masyarakat.date_of_birth)
    $("#admin-nik").val(data.masyarakat.nik)
    $("#admin-active-status").val(data.masyarakat.active_status === "true" ? "Aktif" : "Tidak Aktif")

    // instansi
    $("#admin-banjar").val(data.masyarakat.banjar.name)
    $("#admin-desa-adat").val(data.masyarakat.banjar.desa_adat.name)
    $("#admin-kecamatan").val(data.masyarakat.banjar.desa_adat.kecamatan.name)
    $("#admin-kabupaten").val(data.masyarakat.banjar.desa_adat.kecamatan.kabupaten.name)

    // modal
    $("#edit-id").val(data.masyarakat.id)
    $("#edit-id-banjar").val(data.masyarakat.banjar.id)
    $("#edit-nama").val(data.masyarakat.name)
    $("#edit-avatar-img").attr("src", data.masyarakat.avatar);
    $("#edit-telp").val(data.masyarakat.phone)
    $("#edit-nik").val(data.masyarakat.nik)
    $("#edit-jenis-kelamin").val(data.masyarakat.gender === "Laki-laki" ? "l" : "p")
    $("#edit-status-valid").val(Number(data.masyarakat.valid_status))
    $("#edit-status-aktif").val(Number(data.masyarakat.active_status))
    $("#edit-tanggal-lahir").val(data.masyarakat.date_of_birth)
    $("#edit-jenis-krama").val(data.masyarakat.category === "Krama Wid" ? 0 : obj.category === "Krama Tamiu" ? 1 : 2)


  } else if (status_code === 401) {
    refreshToken(readAdmin)
  }
};
