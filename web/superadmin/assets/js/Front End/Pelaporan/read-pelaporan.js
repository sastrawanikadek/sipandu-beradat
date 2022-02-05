$(document).ready(async () => {
  await readAllPelaporan();
});

const readPelaporan = async () => {
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(`https://sipanduberadat.com/api/pelaporan/`);
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporan();
  }
};

const readPelaporanDarurat = async () => {
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(`https://sipanduberadat.com/api/pelaporan-darurat/`);
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanDarurat();
  }
};

const readPelaporanTamu = async () => {
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(`https://sipanduberadat.com/api/pelaporan-tamu/`);
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
  const pelaporans = await readPelaporan();
  const pelaporanDarurats = await readPelaporanDarurat();
  const pelaporansTamu = await readPelaporanTamu();
  const pelaporanDaruratsTamu = await readPelaporanDaruratTamu();

  $("#tabel-pelaporan tbody").html("");
  [
    ...pelaporans,
    ...pelaporanDarurats,
    ...pelaporansTamu,
    ...pelaporanDaruratsTamu,
  ].map((obj, i) => {
    const row = `
          <tr>
            <td>${i + 1}</td>
            <td style="text-transform:capitalize;">${
              obj.masyarakat ? obj.masyarakat.name : obj.tamu.name
            }</td>
            <td>${obj.jenis_pelaporan.name}</td>
            ${
              obj.jenis_pelaporan.emergency_status
                ? '<td><div class="badge badge-danger">Darurat</div></td>'
                : '<td><div class="badge badge-primary">Keluhan</div></td>'
            }
            ${
              obj.masyarakat
                ? '<td><div class="badge badge-dark">Krama</div></td>'
                : '<td><div class="badge badge-light">Tamu</div></td>'
            }
            <td>${obj.time}</td>
            ${
              obj.status === 0
                ? '<td><div class="badge badge-secondary">Menunggu Validasi</div></td>'
                : obj.status === 1
                ? '<td><div class="badge badge-primary">Sedang Diproses</div></td>'
                : obj.status === -1
                ? '<td><div class="badge badge-danger">Tidak Valid</div></td>'
                : '<td><div class="badge badge-success">Selesai</div></td'
            }
            <td>							 
              <a href="detail_pelaporan.html?view-name=${
                obj.masyarakat ? obj.masyarakat.name : obj.tamu.name
              }&view-avatar=${
      obj.masyarakat ? obj.masyarakat.avatar : obj.tamu.avatar
    }&view-avatar-cover=${
      obj.masyarakat ? obj.masyarakat.avatar : obj.tamu.avatar
    }&view-kategori-pelaporan=${Number(
      obj.jenis_pelaporan.emergency_status
    )}&view-pelapor=${obj.masyarakat ? "Krama" : "Tamu"}&view-status=${
      obj.status
    }&view-time=${obj.time}&title=${obj.title}&name=${
      obj.masyarakat ? obj.masyarakat.name : obj.tamu.name
    }&jenis-pelaporan=${obj.jenis_pelaporan.name}&phone=${
      obj.masyarakat ? obj.masyarakat.phone : obj.tamu.phone
    }&gender=${
      obj.masyarakat
        ? obj.masyarakat.gender === "l"
          ? "Laki-laki"
          : "Perempuan"
        : obj.tamu.gender === "l"
        ? "Laki-laki"
        : "Perempuan"
    }&description=${obj.description}&photo=${obj.photo}&desa-adat=${
      obj.desa_adat.name
    }&kecamatan=${obj.desa_adat.kecamatan.name}&kabupaten=${
      obj.desa_adat.kecamatan.kabupaten.name
    }&latitude=${obj.latitude}&longitude=${obj.longitude}"
              class="btn btn-icon btn-sm btn-primary" title="Detail Pelaporan">
                <i class="fas fa-file-alt"></i>
              </a>
            </td>
          </tr>
      `;
    $("#tabel-pelaporan tbody").append(row);
  });
};
