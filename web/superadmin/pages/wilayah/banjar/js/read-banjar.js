$("#form-banjar").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await readBanjar();
});
const active_status_badges = [
  "<label class='badge badge-primary-red'>Nonaktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];

$(document).ready(async () => {
  $("#tambah-desa-adat").attr("disabled", "disabled");
  const kecamatans = await readKecamatan();
  const desaAdats = await readDesaAdat();
  await readBanjar();

  kecamatans.map((obj) => {
    const option = `<option value="${obj.id}">${obj.name}</option>`;
    $("#tambah-kecamatan").append(option);
    $("#edit-kecamatan").append(option);
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

const readBanjar = async () => {
  startLoading();
  const req = await fetch("https://sipanduberadat.com/api/banjar/");
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    setupFilterDataTable(
      "tabel-banjar",
      [5],
      data.map((obj, i) => [
        i + 1,
        obj.desa_adat.kecamatan.name,
        obj.desa_adat.name,
        obj.name,
        active_status_badges[Number(obj.active_status)],
        `<div class="container-crud">
        <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit" data-toggle="modal"
        data-target="#modal-edit-banjar" data-id="${obj.id}" data-id-kecamatan="${obj.desa_adat.kecamatan.id}" data-id-desa-adat="${obj.desa_adat.id}" data-name="${obj.name}"
               data-status="${obj.active_status}">
               <i class="mdi mdi-pencil"></i>
        </a>
        <a href="#" class="btn btn-inverse-primary-red
         btn-rounded btn-icon btn-action mr-2 btn-delete" title="Delete" data-toggle="modal"
        data-target="#modal-hapus-banjar" data-id="${obj.id}">
        <i class="mdi mdi-delete"></i>
        </a>
    </div>`,
      ])
    );

    stopLoading();

    $("tbody").on("click", ".btn-edit", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      const id_kecamatan = $(e.currentTarget).attr("data-id-kecamatan");
      const id_desa_adat = $(e.currentTarget).attr("data-id-desa-adat");
      const name = $(e.currentTarget).attr("data-name");
      const status = $(e.currentTarget).attr("data-status");

      $("#edit-id").val(id);
      $("#edit-kecamatan").val(id_kecamatan).change();
      $("#edit-desa-adat").val(id_desa_adat);
      $("#edit-banjar").val(name);
      $("#edit-active-status").val(status);
    });

    $("tbody").on("click", ".btn-delete", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id);
    });
  }
};
