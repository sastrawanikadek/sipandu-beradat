$(document).ready(async () => {
  $("#tambah-kecamatan").attr("disabled", "disabled");
  $("#tambah-desa-adat").attr("disabled", "disabled");
  const kabupatens = await readKabupaten();
  const kecamatans = await readKecamatan();
  const desaAdats = await readDesaAdat();

  kabupatens.map((obj) => {
    const option = `<option value="${obj.id}">${obj.name}</option>`;
    $("#tambah-kabupaten").append(option);
    $("#edit-kabupaten").append(option);
  });

  $("#tambah-kabupaten").change((e) => {
    if (e.target.value) {
      $("#tambah-kecamatan").removeAttr("disabled");
      $("#tambah-kecamatan").html("");
      $("#tambah-kecamatan").append(
        "<option value=''>Pilih Kecamatan</option>"
      );
      kecamatans
        .filter((obj) => obj.kabupaten.id === Number(e.target.value))
        .map((obj) => {
          const option = `<option value="${obj.id}">${obj.name}</option>`;
          $("#tambah-kecamatan").append(option);
        });
    } else {
      $("#tambah-kecamatan").attr("disabled", "disabled");
    }
  });
  $("#tambah-kecamatan").change((e) => {
    if (e.target.value) {
      $("#tambah-desa-adat").removeAttr("disabled");
      $("#tambah-desa-adat").html("");
      desaAdats
        .filter((obj) => obj.kecamatan.id === Number(e.target.value))
        .map((obj) => {
          const option = `<option value="${obj.id}">${obj.name}</option>`;
          $("#tambah-desa-adat").append(option);
        });
    } else {
      $("#tambah-desa-adat").attr("disabled", "disabled");
    }
  });
});
const readKabupaten = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/kabupaten/?active_status=true"
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readKabupaten();
  }
};

const readKecamatan = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/kecamatan/?active_status=true"
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readKecamatan();
  }
};

const readDesaAdat = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/desa-adat/?active_status=true"
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readDesaAdat();
  }
};
