$(document).ready(() => {
  formConfig();
});

const formConfig = () => {
  $("#tambah-terapkan-semua").change(function () {
    if ($(this).prop("checked")) {
      $("#input-pecalang").hide();
    } else {
      $("#input-pecalang").show();
    }
  });

  const today = new Date();
  const nextMonth = new Date();
  nextMonth.setMonth(today.getMonth() + 1);
  nextMonth.setDate(1);
  nextMonth.setDate(nextMonth.getDate() - 1);

  $("#date").datepicker({
    format: "yyyy-mm-dd",
    multidate: true,
    todayHighlight: true,
    startDate: new Date(
      `${today.getFullYear()}-${(today.getMonth() + 1)
        .toString()
        .padStart(2, "0")}-01`
    ),
    endDate: new Date(
      `${nextMonth.getFullYear()}-${(nextMonth.getMonth() + 1)
        .toString()
        .padStart(2, "0")}-${nextMonth.getDate().toString().padStart(2, "0")}`
    ),
  });

  $("#edit-date").datepicker({
    format: "yyyy-mm-dd",
    multidate: true,
    todayHighlight: true,
    startDate: new Date(
      `${today.getFullYear()}-${(today.getMonth() + 1)
        .toString()
        .padStart(2, "0")}-01`
    ),
    endDate: new Date(
      `${nextMonth.getFullYear()}-${(nextMonth.getMonth() + 1)
        .toString()
        .padStart(2, "0")}-${nextMonth.getDate().toString().padStart(2, "0")}`
    ),
  });
};

$("#form-tambah-jadwal-pecalang").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await tambahPecalang();
});

const tambahPecalang = async () => {
  const id_pecalang = $("#tambah-pecalang").val();
  const days1 = $("#date").val();
  let hari;

  if (document.getElementById("hari-libur").checked) {
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
