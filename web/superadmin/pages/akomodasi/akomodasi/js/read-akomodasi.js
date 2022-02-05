const active_status_badges = [
  "<label class='badge badge-primary-red'>Nonaktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];

$(document).ready(async () => {
  $("#tambah-kecamatan").attr("disabled", "disabled");
  $("#tambah-desa-adat").attr("disabled", "disabled");
  $("#tambah-kecamatan1").attr("disabled", "disabled");
  $("#tambah-desa-adat1").attr("disabled", "disabled");
  $("#tambah-desa-adat1").attr("disabled", "disabled");
  const kabupatens = await readKabupaten();
  const kecamatans = await readKecamatan();
  const desaAdats = await readDesaAdat();
  await readAkomodasi();

  $("#tambah-profil-pic").change((e) => {
    if (e.currentTarget.files.length > 0) {
      $("#label-tambah-profil-pic").text(e.currentTarget.files[0].name);
    } else {
      $("#label-tambah-profil-pic").text("Select file");
    }
  });

  $("#tambah-cover-pic").change((e) => {
    if (e.currentTarget.files.length > 0) {
      $("#label-tambah-cover-pic").text(e.currentTarget.files[0].name);
    } else {
      $("#label-tambah-cover-pic").text("Select file");
    }
  });

  $("#edit-profil-pic").change((e) => {
    if (e.currentTarget.files.length > 0) {
      $("#label-edit-profil-pic").text(e.currentTarget.files[0].name);
    } else {
      $("#label-edit-profil-pic").text("Select file");
    }
  });

  $("#edit-cover-pic").change((e) => {
    if (e.currentTarget.files.length > 0) {
      $("#label-edit-cover-pic").text(e.currentTarget.files[0].name);
    } else {
      $("#label-edit-cover-pic").text("Select file");
    }
  });

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

  $("#edit-kabupaten").change((e) => {
    if (e.target.value) {
      $("#edit-kecamatan").removeAttr("disabled");
      $("#edit-kecamatan").html("");
      $("#edit-kecamatan").append("<option value=''>Pilih Kecamatan</option>");
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

  $("#edit-kecamatan").change((e) => {
    if (e.target.value) {
      $("#edit-desa-adat").removeAttr("disabled");
      $("#edit-desa-adat").html("");
      desaAdats
        .filter((obj) => obj.kecamatan.id === Number(e.target.value))
        .map((obj) => {
          const option = `<option value="${obj.id}">${obj.name}</option>`;
          $("#edit-desa-adat").append(option);
        });
    } else {
      $("#edit-desa-adat").attr("disabled", "disabled");
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

const readAkomodasi = async () => {
  startLoading();
  const req = await fetch("https://sipanduberadat.com/api/akomodasi/");
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    setupFilterDataTable(
      "tabel-akomodasi",
      [5],
      data.map((obj, i) => [
        i + 1,
        obj.name,
        obj.desa_adat.name,
        obj.location,
        active_status_badges[Number(obj.active_status)],
        `<div class="container-crud">
        <a href="#" class="btn btn-inverse-success btn-rounded btn-icon btn-action mr-2 btn-super-admin" title="Super Admin" data-toggle="modal"
        data-target="#modal-tambah-super-admin" data-id="${obj.id}">
            <i class="mdi mdi-account-check"></i>
        </a>
        <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit" data-toggle="modal"
        data-target="#modal-edit-akomodasi" data-id="${obj.id}" data-id-desa-adat="${obj.desa_adat.id}" data-id-kabupaten="${obj.desa_adat.kecamatan.kabupaten.id}" data-id-kecamatan="${obj.desa_adat.kecamatan.id}" data-name="${obj.name}"
              data-location="${obj.location}" data-description="${obj.description}" data-status="${obj.active_status}">
              <i class="mdi mdi-pencil"></i>
        </a>
        <a href="#" class="btn btn-inverse-primary-red btn-rounded btn-icon btn-action mr-2 btn-delete" title="Delete" data-toggle="modal"
        data-target="#modal-hapus-akomodasi" data-id="${obj.id}">
        <i class="mdi mdi-delete"></i>
        </a>
    </div>`,
      ])
    );

    stopLoading();

    $("tbody").on("click", ".btn-super-admin", (e) => {
      const id = $(e.currentTarget).attr("data-id");

      $("#edit-id").val(id);
    });

    $("tbody").on("click", ".btn-edit", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      const id_desa_adat = $(e.currentTarget).attr("data-id-desa-adat");
      const id_kabupaten = $(e.currentTarget).attr("data-id-kabupaten");
      const id_kecamatan = $(e.currentTarget).attr("data-id-kecamatan");
      const name = $(e.currentTarget).attr("data-name");
      const location = $(e.currentTarget).attr("data-location");
      const description = $(e.currentTarget).attr("data-description");
      const status = $(e.currentTarget).attr("data-status");

      $("#edit-id").val(id);
      $("#edit-kabupaten").val(id_kabupaten).change();
      $("#edit-kecamatan").val(id_kecamatan).change();
      $("#edit-desa-adat").val(id_desa_adat);
      $("#edit-akomodasi").val(name);
      $("#edit-alamat").val(location);
      $("#edit-deskripsi").val(description);
      $("#edit-active-status").val(status);
    });
    $("tbody").on("click", ".btn-delete", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id);
    });
  }
};
