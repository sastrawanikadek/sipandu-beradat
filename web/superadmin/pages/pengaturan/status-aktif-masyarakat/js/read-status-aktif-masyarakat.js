const active_status_badges = [
  "<label class='badge badge-primary-red'>Nonaktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];
const status_badges = [
  "<label class='badge badge-primary-red'>Nonaktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];

$(document).ready(() => {
  readStatusMasyarakat();
});

const readStatusMasyarakat = async () => {
  startLoading();
  const req = await fetch(
    "https://sipanduberadat.com/api/status-aktif-masyarakat/"
  );
  const { status_code, data, message } = await req.json();

  stopLoading();

  if (status_code === 200) {
    $(".table-datatable").DataTable({
      fixedHeader: {
        header: true,
        footer: true,
      },
      columnDefs: [{ orderable: false, targets: [4] }],
      data: data.map((obj, i) => [
        i + 1,
        obj.name,
        status_badges[Number(obj.status)],
        active_status_badges[Number(obj.active_status)],
        `<div class="container-crud">
        <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit" data-toggle="modal"
            data-target="#modal-edit-status-aktif-masyarakat" data-id="${obj.id}" data-name="${obj.name}" data-status="${obj.status}" data-active-status="${obj.active_status}">
<i class="mdi mdi-pencil"></i>
        </a>
        <a href="#" class="btn btn-inverse-primary-red btn-rounded btn-icon btn-action mr-2 btn-delete" title="Delete" data-toggle="modal"
        data-target="#modal-hapus-status-aktif-masyarakat" data-id="${obj.id}">
        <i class="mdi mdi-delete"></i>
        </a>
    </div>`,
      ]),
    });

    $("tbody").on("click", ".btn-edit", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      const name = $(e.currentTarget).attr("data-name");
      const status = $(e.currentTarget).attr("data-status");
      const active_status = $(e.currentTarget).attr("data-active-status");

      $("#edit-id").val(id);
      $("#edit-nama-status").val(name);
      $("#edit-status-masyarakat").val(status);
      $("#edit-active-status").val(active_status);
    });

    $("tbody").on("click", ".btn-delete", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id);
    });
  }
};
