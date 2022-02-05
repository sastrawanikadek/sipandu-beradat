$(document).ready(async () => {
  await readAllPelaporan();
});

const emergency_status_badges = [
  "<label class='badge badge-info'>Keluhan</label>",
  "<label class='badge badge-primary-red'>Darurat</label>",
];

const status_badges = [
  "<label class='badge badge-primary-red'>Tidak Valid</label>",
  "<label class='badge badge-primary-orange'>Menunggu Validasi</label>",
  "<label class='badge badge-info'>Sedang Diproses</label>",
  "<label class='badge badge-success'>Selesai</label>",
];

const readPelaporanTamu = async () => {
  const idAkomodasi = localStorage.getItem("id_akomodasi");
  const req = await fetch(
    `https://sipanduberadat.com/api/pelaporan-tamu/?id_akomodasi=${idAkomodasi}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporan();
  }
};

const readPelaporanDaruratTamu = async () => {
  const idAkomodasi = localStorage.getItem("id_akomodasi");
  const req = await fetch(
    `https://sipanduberadat.com/api/pelaporan-darurat-tamu/?id_akomodasi=${idAkomodasi}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanDarurat();
  }
};

const readAllPelaporan = async () => {
  const pelaporansTamu = (await readPelaporanTamu()).map((obj) => ({
    ...obj,
    report_type: "pelaporan-tamu",
  }));
  const pelaporanDaruratsTamu = (await readPelaporanDaruratTamu()).map(
    (obj) => ({
      ...obj,
      report_type: "pelaporan-darurat-tamu",
    })
  );
  let data = [...pelaporanDaruratsTamu, ...pelaporansTamu];

  setupFilterDataTable(
    "tabel-pelaporan",
    [7],
    data.map((obj, i) => [
      i + 1,
      obj.time,
      obj.tamu.name,
      obj.jenis_pelaporan.name,
      emergency_status_badges[Number(obj.jenis_pelaporan.emergency_status)],
      obj.desa_adat.name,
      status_badges[obj.status == -1 ? 0 : obj.status + 1],
      `<div class="container-crud">
      <a href="detail-pelaporan.html?id=${obj.id}&report_type=${obj.report_type}&latitude=${obj.latitude}&longitude=${obj.longitude}" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-detail" title="Detail Pelaporan" >
<i class="mdi mdi-account-card-details"></i>
      </a>
  </div>`,
    ])
  );
  stopLoading();
};
