$("#form-jenis-pelaporan").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await readJenisPelaporan();
});
const active_status_badges = [
  "<label class='badge badge-primary-red'>Nonaktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];

const emergency_status_badges = [
  "<label class='badge badge-info'>Keluhan</label>",
  "<label class='badge badge-primary-red'>Darurat</label>",
];

$(document).ready(() => {
  readJenisPelaporan();

  $("#tambah-icon").change((e) => {
    if (e.currentTarget.files.length > 0) {
      $("#label-tambah-icon").text(e.currentTarget.files[0].name);
    } else {
      $("#label-tambah-icon").text("Select file");
    }
  });

  $("#edit-icon").change((e) => {
    if (e.currentTarget.files.length > 0) {
      $("#label-edit-icon").text(e.currentTarget.files[0].name);
    } else {
      $("#label-edit-icon").text("Select file");
    }
  });
});

const readJenisPelaporan = async () => {
  startLoading();
  const req = await fetch("https://sipanduberadat.com/api/jenis-pelaporan/");
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    setupFilterDataTable(
      "tabel-jenis-pelaporan",
      [3],
      data.map((obj, i) => [
        i + 1,
        obj.name,
        `<img src="${obj.icon}"></img>`,
        emergency_status_badges[Number(obj.emergency_status)],
        active_status_badges[Number(obj.active_status)],
        `<div class="container-crud">
        <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit" data-toggle="modal"
        data-target="#modal-edit-jenis-pelaporan" data-id="${obj.id}" data-name="${obj.name}" data-emergency-status="${obj.emergency_status}" data-status="${obj.active_status}">
<i class="mdi mdi-pencil"></i>
        </a>
        <a href="#" class="btn btn-inverse-primary-red btn-rounded btn-icon btn-action mr-2 btn-delete" title="Delete" data-toggle="modal"
        data-target="#modal-hapus-jenis-pelaporan" data-id="${obj.id}">
        <i class="mdi mdi-delete"></i>
        </a>
    </div>`,
      ])
    );

    stopLoading();

    $("tbody").on("click", ".btn-edit", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      const name = $(e.currentTarget).attr("data-name");
      const emergency_status = $(e.currentTarget).attr("data-emergency-status");
      const status = $(e.currentTarget).attr("data-status");

      $("#edit-id").val(id);
      $("#edit-jenis-pelaporan").val(name);
      $("#edit-status-pelaporan").val(emergency_status);
      $("#edit-active-status").val(status);
    });

    $("tbody").on("click", ".btn-delete", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id);
    });
  }
};
