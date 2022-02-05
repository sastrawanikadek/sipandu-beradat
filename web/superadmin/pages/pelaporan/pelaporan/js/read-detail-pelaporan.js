$(document).ready(() => {
  readDetailPelaporan();
});

$("#go-back").on("click", function () {
  window.history.back();
});

const readDetailPelaporan = async () => {
  let link;
  const id = getParameterByName("id_pelaporan");
  const jenis_krama = getParameterByName("jenis-krama");
  const emergency_status = getParameterByName("emergency-status");

  if (emergency_status == 1 && jenis_krama == 1) {
    link = `https://sipanduberadat.com/api/pelaporan-darurat-tamu/find/?id_pelaporan_darurat_tamu=${id}`;
  } else if (emergency_status == 0 && jenis_krama == 1) {
    link = `https://sipanduberadat.com/api/pelaporan-tamu/find/?id_pelaporan_tamu=${id}`;
  } else if (emergency_status == 1 && jenis_krama == 0) {
    link = `https://sipanduberadat.com/api/pelaporan-darurat/find/?id_pelaporan_darurat=${id}`;
  } else if (emergency_status == 0 && jenis_krama == 0) {
    link = `https://sipanduberadat.com/api/pelaporan/find/?id_pelaporan=${id}`;
  }
  const req = await fetch(link);
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    let activeClass = " active";

    if (Number(data.jenis_pelaporan.emergency_status) == 0) {
      const row = `
        <div class="carousel-item${activeClass}">
          <img class="d-block w-100 modal-img" data-toggle="modal" data-target="#modal-show-img" src="${data.photo}">
        </div>
      `;
      $("#detail-pelaporan-img").append(row);
      activeClass = "";
    }

    data.pecalang_reports.map((obj, i) => {
      const row = `
          <div class="carousel-item${activeClass}">
            <img class="d-block w-100 modal-img" data-toggle="modal" data-target="#modal-show-img" src="${obj.photo}">
          </div>
      `;
      $("#detail-pelaporan-img").append(row);
      activeClass = "";
    });

    data.petugas_reports.map((obj, i) => {
      const row = `
        <div class="carousel-item${activeClass}">
          <img class="d-block w-100 modal-img" data-toggle="modal" data-target="#modal-show-img" src="${obj.photo}">
        </div>
      `;
      $("#detail-pelaporan-img").append(row);
      activeClass = "";
    });

    if (JSON.parse(data.jenis_pelaporan.emergency_status)) {
      $("#form-title").hide();
      $("#form-description").hide();
    }

    if (
      JSON.parse(data.jenis_pelaporan.emergency_status) &&
      data.status === 0
    ) {
      $("#form-photo").hide();
    }

    $("#view-avatar").attr(
      "src",
      data.masyarakat ? data.masyarakat.avatar : data.tamu.avatar
    );
    $("#view-avatar-cover").attr(
      "src",
      data.masyarakat ? data.masyarakat.avatar : data.tamu.avatar
    );
    $("#view-name").text(
      data.masyarakat ? data.masyarakat.name : data.tamu.name
    );
    $("#view-pelapor").text(data.masyarakat ? "Krama" : "Tamu");
    $("#view-kategori-pelaporan").text(
      JSON.parse(data.jenis_pelaporan.emergency_status) ? "Darurat" : "Keluhan"
    );
    $("#view-status").html(
      data.status === 0
        ? '<i class="mdi mdi-circle fa-xs text-primary-orange mr-2"></i><span>Menunggu Validasi</span>'
        : data.status === 1
        ? '<i class="mdi mdi-circle fa-xs text-info mr-2"></i><span>Sedang Diproses</span>'
        : data.status === -1
        ? '<i class="mdi mdi-circle fa-xs text-primary-red mr-2"></i><span>Tidak Valid</span>'
        : '<i class="mdi mdi-circle fa-xs text-success mr-2"></i><span>Selesai</span>'
    );

    const bulan = [
      "Jan",
      "Feb",
      "Mar",
      "Apr",
      "Mei",
      "Jun",
      "Jul",
      "Agu",
      "Sep",
      "Okt",
      "Nov",
      "Des",
    ];
    const tanggal = new Date(data.time);
    const full_date = `${tanggal.getDate()} ${
      bulan[tanggal.getMonth()]
    } ${tanggal.getFullYear()}`;

    $("#view-time").text(full_date);
    $("#detail-title").val(data.title);
    $("#detail-nama").val(
      data.masyarakat ? data.masyarakat.name : data.tamu.name
    );
    $("#detail-jenis-pelaporan").val(data.jenis_pelaporan.name);
    $("#detail-description").val(data.description);
    $("#detail-phone").val(
      data.masyarakat ? data.masyarakat.phone : data.tamu.phone
    );
    $("#detail-gender").val(
      data.masyarakat
        ? data.masyarakat.gender === "l"
          ? "Laki-laki"
          : "Perempuan"
        : data.tamu.gender === "l"
        ? "Laki-laki"
        : "Perempuan"
    );
    $("#detail-desa-adat").val(data.desa_adat.name);
    $("#detail-kecamatan").val(data.desa_adat.kecamatan.name);
    $("#detail-kabupaten").val(data.desa_adat.kecamatan.kabupaten.name);
  }
};

$(".carousel-inner").on("click", ".modal-img", function () {
  const img_url = $(this).attr("src");
  $(".show-img").attr("src", img_url);
});
