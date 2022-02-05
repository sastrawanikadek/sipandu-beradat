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
  const data1 = await readAllPelaporan();
});

const readPelaporanDarurat = async () => {
  const req = await fetch(`https://sipanduberadat.com/api/pelaporan-darurat/`);
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanDarurat();
  }
};

const readPelaporan = async () => {
  const req = await fetch(`https://sipanduberadat.com/api/pelaporan/`);
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporan();
  }
};

const readPelaporanTamu = async () => {
  const req = await fetch(`https://sipanduberadat.com/api/pelaporan-tamu/`);
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanTamu();
  }
};

const readPelaporanDaruratTamu = async () => {
  const req = await fetch(
    `https://sipanduberadat.com/api/pelaporan-darurat-tamu/`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanDaruratTamu();
  }
};

const readAllPelaporan = async () => {
  startLoading();
  const darurats = await readPelaporanDarurat();
  const keluhans = await readPelaporan();
  const keluhanTamus = await readPelaporanTamu();
  const daruratTamus = await readPelaporanDaruratTamu();
  const data = [...darurats, ...keluhans, ...keluhanTamus, ...daruratTamus];

  setupFilterDataTable(
    "tabel-pelaporan",
    [8],
    data.map((obj, i) => [
      i + 1,
      obj.time,
      obj.masyarakat ? obj.masyarakat.name : obj.tamu.name,
      masyarakat_badges[obj.masyarakat ? 0 : 1],
      obj.jenis_pelaporan.name,
      emergency_status_badges[Number(obj.jenis_pelaporan.emergency_status)],
      obj.desa_adat.name,
      status_badges[obj.status + 1],
      `<div class="container-crud">
       <a href="pages/pelaporan/pelaporan/detail-pelaporan.html?id_pelaporan=${
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
  stopLoading();
};
