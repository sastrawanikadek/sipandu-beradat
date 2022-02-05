const masyarakat_badges = [
  "<label class='badge badge-dark'>Krama</label>",
  "<label class='badge badge-info'>Tamu</label>",
];
const emergency_status_badges = [
  "<label class='badge badge-info'>Keluhan</label>",
  "<label class='badge badge-primary-red'>Darurat</label>",
];
const status_texts = [
  "Tidak Valid",
  "Menunggu Validasi",
  "Sedang Diproses",
  "Selesai",
];
const status_badges = [
  "<label class='badge badge-primary-red'>Tidak Valid</label>",
  "<label class='badge badge-primary-orange'>Menunggu Validasi</label>",
  "<label class='badge badge-info'>Sedang Diproses</label>",
  "<label class='badge badge-success'>Selesai</label>",
];

$(document).ready(async () => {
  if (!localStorage.getItem("login")) {
    window.location.href = "https://sipanduberadat.com/desa-adat/pages/login/";
  }
  await readAllPelaporan();
  await totalAnalytics();
});

const readPelaporanDarurat = async () => {
  startLoading();
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/pelaporan-darurat/?id_desa=${idDesa}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    stopLoading();
    return data;
  } else {
    readPelaporanDarurat();
  }
};

const readPelaporan = async () => {
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/pelaporan/?id_desa=${idDesa}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporan();
  }
};

const readPelaporanTamu = async () => {
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/pelaporan-tamu/?id_desa=${idDesa}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanTamu();
  }
};

const readPelaporanDaruratTamu = async () => {
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/pelaporan-darurat-tamu/?id_desa=${idDesa}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanDaruratTamu();
  }
};

const readAllPelaporan = async () => {
  const darurats = await readPelaporanDarurat();
  const keluhans = await readPelaporan();
  const keluhanTamus = await readPelaporanTamu();
  const daruratTamus = await readPelaporanDaruratTamu();
  const data = [...darurats, ...keluhans, ...keluhanTamus, ...daruratTamus];

  setupFilterDataTable(
    "tabel-pelaporan",
    [7],
    data.map((obj, i) => [
      i + 1,
      obj.time,
      obj.masyarakat ? obj.masyarakat.name : obj.tamu.name,
      masyarakat_badges[obj.masyarakat ? 0 : 1],
      obj.jenis_pelaporan.name,
      emergency_status_badges[Number(obj.jenis_pelaporan.emergency_status)],
      status_badges[obj.status + 1],
      `<div class="container-crud">
		 		<a href="pages/pelaporan/detail-pelaporan.html?id_pelaporan=${
          obj.id
        }&jenis-krama=${obj.masyarakat ? 0 : 1}&emergency-status=${Number(
        obj.jenis_pelaporan.emergency_status
      )}&latitude=${obj.latitude}&longitude=${obj.longitude}&jenis-pelaporan=${
        obj.jenis_pelaporan.name
      }" 
					class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-detail" title="Detail">
 						<i class="mdi mdi-file-document-box"></i>
		 		</a>
			</div>`,
    ])
  );
};

const totalAnalytics = async () => {
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/admin-desa-adat/analytic/?id_desa=${idDesa}`,
    {
      method: "GET",
    }
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    $("#total-pecalang").html(data.total_pecalang);
    $("#total-e-kulkul").html(data.total_sirine);
    $("#total-krama-desa").html(data.total_masyarakat);
    $("#total-today-pelaporan").html(data.total_today_pelaporan);
    $("#total-block").html(data.total_block);
    $("#total-banjar").html(data.total_banjar);

    $("#total-pelaporan-darurat").html(data.total_pelaporan_darurat);
    $("#total-pelaporan").html(data.total_pelaporan);
    $("#total-krama-wid").html(data.masyarakat_categories["Krama Wid"] ?? 0);
    $("#total-krama-tamiu").html(
      data.masyarakat_categories["Krama Tamiu"] ?? 0
    );
    $("#total-tamiu").html(data.masyarakat_categories["Tamiu"] ?? 0);
  } else if (status_code === 401) {
    totalAnalytics();
  }
};
