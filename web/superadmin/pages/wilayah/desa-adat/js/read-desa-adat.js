const active_status_badges = [
  "<label class='badge badge-primary-red'>Nonaktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];

let banjars = [];

$(document).ready(async () => {
  $("#tambah-kecamatan").attr("disabled", "disabled");
  $("#tambah-kecamatan1").attr("disabled", "disabled");
  const kabupatens = await readKabupaten();
  const kecamatans = await readKecamatan();
  banjars = await readBanjar();
  await readDesaAdat();

  $("#admin-profil-pic").change((e) => {
    if (e.currentTarget.files.length > 0) {
      $("#label-admin-profil-pic").text(e.currentTarget.files[0].name);
    } else {
      $("#label-admin-profil-pic").text("Select file");
    }
  });

  kabupatens.map((obj) => {
    const option = `<option value="${obj.id}">${obj.name}</option>`;
    $("#tambah-kabupaten").append(option);
    $("#edit-kabupaten").append(option);
  });

  $("#tambah-kabupaten").change((e) => {
    if (e.target.value) {
      $("#tambah-kecamatan").removeAttr("disabled");
      $("#tambah-kecamatan").html("");
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

  $("#edit-kabupaten").change((e) => {
    if (e.target.value) {
      $("#edit-kecamatan").removeAttr("disabled");
      $("#edit-kecamatan").html("");
      kecamatans
        .filter((obj) => obj.kabupaten.id === Number(e.target.value))
        .map((obj) => {
          const option = `<option value="${obj.id}">${obj.name}</option>`;
          $("#edit-kecamatan").append(option);
        });
    } else {
      $("#edit-kecamatan").attr("disabled", "disabled");
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

const readBanjar = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/banjar/?active_status=true"
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readBanjar();
  }
};

const readDesaAdat = async () => {
  startLoading();
  const req = await fetch("https://sipanduberadat.com/api/desa-adat/");
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    setupFilterDataTable(
      "tabel-desa-adat",
      [7],
      data.map((obj, i) => [
        i + 1,
        obj.kecamatan.kabupaten.name,
        obj.kecamatan.name,
        obj.name,
        obj.latitude,
        obj.longitude,
        active_status_badges[Number(obj.active_status)],
        `<div class="container-crud">
        <a href="#" class="btn btn-inverse-success btn-rounded btn-icon btn-action mr-2 btn-super-admin" title="Super Admin" data-toggle="modal"
        data-target="#modal-tambah-super-admin" data-id="${obj.id}">
        <i class="mdi mdi-account-check"></i>
        </a>
        <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit" data-toggle="modal"
data-target="#modal-edit-desa-adat" data-id="${obj.id}" data-id-kabupaten="${obj.kecamatan.kabupaten.id}" data-id-kecamatan="${obj.kecamatan.id}" data-name="${obj.name}"
              data-latitude="${obj.latitude}" data-longitude="${obj.longitude}" data-status="${obj.active_status}">
<i class="mdi mdi-pencil"></i>
        </a>
        <a href="#" class="btn btn-inverse-primary-red btn-rounded btn-icon btn-action mr-2 btn-delete" title="Delete" data-toggle="modal"
        data-target="#modal-hapus-desa-adat" data-id="${obj.id}">
        <i class="mdi mdi-delete"></i>
        </a>
    </div>`,
      ])
    );

    stopLoading();

    $("tbody").on("click", ".btn-super-admin", (e) => {
      $("#admin-banjar").html("");
      const id = $(e.currentTarget).attr("data-id");

      banjars
        .filter((obj) => obj.desa_adat.id === Number(id))
        .map((obj) => {
          const option = `<option value="${obj.id}">${obj.name}</option>`;
          $("#admin-banjar").append(option);
        });
    });

    $("tbody").on("click", ".btn-edit", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      const id_kabupaten = $(e.currentTarget).attr("data-id-kabupaten");
      const id_kecamatan = $(e.currentTarget).attr("data-id-kecamatan");
      const name = $(e.currentTarget).attr("data-name");
      const latitude = $(e.currentTarget).attr("data-latitude");
      const longitude = $(e.currentTarget).attr("data-longitude");
      const status = $(e.currentTarget).attr("data-status");

      $("#edit-id").val(id);
      $("#edit-kabupaten").val(id_kabupaten).change();
      $("#edit-kecamatan").val(id_kecamatan);
      $("#edit-desa-adat").val(name);
      $("#edit-latitude").val(latitude);
      $("#edit-longitude").val(longitude);
      $("#edit-active-status").val(status);
    });

    $("tbody").on("click", ".btn-delete", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id);
    });
  }
};
