//

$("#form-edit-jadwal-pecalang").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await editPecalang();
});

const editPecalang = async () => {
  const id_pecalang = $("#edit-id-jadwal-pecalang").val();
  const days1 = $("#edit-date").val();
  var checkBox = document.getElementById("myCheck");
  var hari;
  // $("input:checkbox[name=type]:checked").each(function () {
  //   arrayDays.push($(this).val());
  // });
  if (document.getElementById("edit-hari-libur").checked) {
    hari = true;
  } else {
    hari = false;
  }

  const fd = new FormData();
  fd.append("id_pecalang", id_pecalang);
  fd.append("holiday_status", hari);
  fd.append("days", JSON.stringify(days1.split(",")));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);
  const req = await fetch(
    "https://sipanduberadat.com/api/jadwal-pecalang/create/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    await readJadwalPecalang();
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
    refreshToken(tambahPecalang);
  }
};
