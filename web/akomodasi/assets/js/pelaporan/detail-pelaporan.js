const id = getParameterByName("id");
const report_type = getParameterByName("report_type");

const readPelaporanTamu = async () => {
  const req = await fetch(
    `https://sipanduberadat.com/api/pelaporan-tamu/find/?id_pelaporan_tamu=${id}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanTamu();
  }
};

const readPelaporanDaruratTamu = async () => {
  const req = await fetch(
    `https://sipanduberadat.com/api/pelaporan-darurat-tamu/find/?id_pelaporan_darurat_tamu=${id}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanDaruratTamu();
  }
};

const readDetailPelaporan = async () => {
  let data = [];
  if (report_type == "pelaporan-tamu") {
    data = await readPelaporanTamu();
    kategori = "keluhan";
  } else {
    data = await readPelaporanDaruratTamu();
    kategori = "darurat";
  }

  if (kategori == "darurat") {
    $("#form-title").hide();
    $("#form-description").hide();
  }

  if (data.status === 0 && kategori === "darurat") {
    $("#form-photo").hide();
  }

  let month = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "July",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "December",
  ];

  const date = new Date(data.time);

  $("#detail-nama").val(data.tamu.name);
  $("#detail-kategori-pelaporan").html(kategori);
  $("#detail-jenis-pelaporan").val(data.jenis_pelaporan.name);
  $("#detail-title").attr("value", data.title);
  $("#detail-description").val(data.description);
  $("#detail-phone").val(data.tamu.phone);
  $("#detail-gender").val(data.tamu.gender == "l" ? "laki-laki" : "perempuan");
  $("#detail-akomodasi").val(data.tamu.akomodasi.name);
  $("#detail-desa-adat").val(data.desa_adat.name);
  $("#detail-kecamatan").val(data.desa_adat.kecamatan.name);
  $("#detail-kabupaten").val(data.desa_adat.kecamatan.kabupaten.name);

  $("#view-name").html(data.tamu.name);
  $("#view-status").html(
    data.status == 0
      ? '<label class="badge badge-primary-orange">Menunggu Validasi</label>'
      : data.status == 1
      ? '<label class="badge badge-info">Sedang Diproses</label>'
      : data.status == -1
      ? '<label class="badge badge-primary-red">Tidak Valid</label>'
      : '<label class="badge badge-success">Selesai</label>'
  );
  $("#view-kategori-pelaporan").html(kategori);
  $("#view-time").html(
    `${date.getDate()} ${month[date.getMonth()]} ${date.getFullYear()}`
  );
  $("#view-avatar").attr("src", `${data.tamu.avatar}`);
  $("#view-avatar-cover").attr("src", `${data.tamu.avatar}`);

  let activeClass = " active";
  data.pecalang_reports.map((obj, i) => {
    const photo = `
      <div class="carousel-item${activeClass}">
        <img class="d-block w-100 modal-img" data-toggle="modal" data-target="#modal-show-img" src="${obj.photo}">
      </div>
    `;
    $("#image").append(photo);
    activeClass = "";
  });
  data.petugas_reports.map((obj, i) => {
    const photo = `
      <div class="carousel-item${activeClass}">
        <img class="d-block w-100 modal-img" data-toggle="modal" data-target="#modal-show-img" src="${obj.photo}">
      </div>
    `;
    $("#image").append(photo);
    activeClass = "";
  });

  if (!data.jenis_pelaporan.emergency_status) {
    const photo = `
    <div class="carousel-item${activeClass}">
      <img class="d-block w-100 modal-img" data-toggle="modal" data-target="#modal-show-img" src="${data.photo}">
    </div>
    `;
    $("#image").append(photo);
    activeClass = "";
  }
};

$(document).ready(() => {
  readDetailPelaporan();
});

$("tbody").on("click", ".modal-img", function () {
  $("#modal-show-img").modal("show");
  const img_url = $(this).attr("src");
  $(".show-img").attr("src", img_url);
});

$(".carousel-inner").on("click", ".modal-img", function () {
  const img_url = $(this).attr("src");
  $(".show-img").attr("src", img_url);
});
