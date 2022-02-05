$(document).ready(async () => {
  await readBanjar();
  $("#filter-status-aktif").change((e) => {
    readBanjar();
  });
});

const active_status_badges = [
  "<label class='badge badge-primary-red status-aktif'>Nonaktif</label>",
  "<label class='badge badge-success status-aktif'>Aktif</label>",
];

const readBanjar = async () => {
  const idDesa = localStorage.getItem("id_desa");
  filter_active_status = $("#filter-status-aktif").val();

  let arraysemen = [];

  startLoading();
  const req = await fetch(
    `https://sipanduberadat.com/api/banjar/?id_desa=${idDesa}`
  );
  const { status_code, data } = await req.json();

  var dataArray = [filter_active_status];

  if (filter_active_status == 2) {
    data1 = data;
  } else {
    for (var c = 0; c < dataArray.length; c++) {
      if (dataArray[c] !== 2) {
        arraysemen.push(c);
      }
    }
    for (var b = 0; b < arraysemen.length; b++) {
      if (b === 0) {
        data1 = data.filter(function filterss(data) {
          var arrayreturn = [data.active_status == filter_active_status];
          return arrayreturn[arraysemen[b]];
        });
      }
    }
  }

  if (status_code === 200) {
    setupFilterDataTable(
      "tabel-banjar",
      [3],
      data.map((obj, i) => [
        i + 1,
        obj.name,
        active_status_badges[Number(obj.active_status)],
        `<div class="container-crud">
          <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit" data-toggle="modal"
            data-target="#modal-edit-banjar" 
            data-id="${obj.id}"
            data-id-desa="${obj.desa_adat.id}"
            data-name="${obj.name}"
            data-active-status="${obj.active_status}">
            <i class="mdi mdi-pencil"></i>
          </a>
          <a href="#" class="btn btn-inverse-danger btn-rounded btn-icon btn-action mr-2 btn-delete" title="Delete" data-toggle="modal"
            data-target="#modal-hapus-banjar" data-id="${obj.id}">
            <i class="mdi mdi-delete"></i>
          </a>
        </div>`,
      ])
    );

    $("#filter-status-aktif").on("change", function () {
      var value = $(this).val().toLowerCase();
      $("tbody tr").filter(function () {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
      });
    });

    stopLoading();
    $("tbody").on("click", ".btn-edit", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      const id_desa = $(e.currentTarget).attr("data-id-desa");
      const name = $(e.currentTarget).attr("data-name");
      const active_status = $(e.currentTarget).attr("data-active-status");

      $("#edit-id").val(id);
      $("#edit-id-desa").val(id_desa);
      $("#edit-name").val(name);
      $("#edit-active-status").val(active_status).change();
    });

    $("tbody").on("click", ".btn-delete", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id);
    });
  }
};
