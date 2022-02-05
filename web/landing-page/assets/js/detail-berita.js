function getParameterByName(name) {
  name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
  var regexS = "[\\?&]" + name + "=([^&#]*)";
  var regex = new RegExp(regexS);
  var results = regex.exec(window.location.href);
  if (results == null) return "";
  else return decodeURIComponent(results[1].replace(/\+/g, " "));
}

const id = getParameterByName("id");
const report_type = getParameterByName("report_type");

const readPelaporanMasyarakat = async () => {
  const req = await fetch(
    `https://sipanduberadat.com/api/pelaporan/find/?id_pelaporan=${id}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanMasyarakat();
  }
};

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

const readPelaporanDaruratMasyarakat = async () => {
  const req = await fetch(
    `https://sipanduberadat.com/api/pelaporan-darurat/find/?id_pelaporan_darurat=${id}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanDaruratMasyarakat();
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

const readBerita = async () => {
  let data = [];
  if (report_type == "pelaporan-masyarakat") {
    data = await readPelaporanMasyarakat();
    kategori = "keluhan";
  } else if (report_type == "pelaporan-darurat-masyarakat") {
    data = await readPelaporanDaruratMasyarakat();
    kategori = "darurat";
  } else if (report_type == "pelaporan-tamu") {
    data = await readPelaporanTamu();
    kategori = "keluhan";
  } else {
    data = await readPelaporanDaruratTamu();
    kategori = "darurat";
  }

  $("#title").html(kategori != "darurat" ? data.title : "Darurat");
  $("#description").html(
    kategori != "darurat"
      ? data.description
      : "Tidak ada Deskripsi dikarenakan pelaporan darurat"
  );
  $("#kategori").html(kategori);
  $("#tempat-kejadian").html(
    `${data.desa_adat.name} , ${data.desa_adat.kecamatan.kabupaten.name}`
  );
  $("#jenis-pelaporan").html(data.jenis_pelaporan.name);

  data.pecalang_reports.map((obj, i) => {
    const photo = `
            <img src="${obj.photo}" class="w-100 img-fluid" alt="">
		`;
    $("#image").append(photo);
  });
  data.petugas_reports.map((obj, i) => {
    const photo = `
            <img src="${obj.photo}" class="w-100 img-fluid" alt="">
		`;
    $("#image").append(photo);
  });

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
  
  $("#tanggal-kejadian").html(
    `${date.getDate()} ${month[date.getMonth()]} , ${date.getFullYear()}`
  );
};

$(document).ready(() => {
  readBerita();
});
