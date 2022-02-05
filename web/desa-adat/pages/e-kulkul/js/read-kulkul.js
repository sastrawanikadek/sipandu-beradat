$(document).ready(async () => {
  await readKulkul();
});

const active_status_badges = [
  "<label class='badge badge-secondary'>Nonaktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];

const readKulkul = async () => {
  startLoading();
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/sirine-desa/?id_desa=${idDesa}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    setupFilterDataTable(
      "tabel-kulkul",
      [2, 5],
      data.map((obj, i) => [
        i + 1,
        obj.code,
        `<div style="background-size:cover; overflow:hidden; width:60px; height:60px">
            <img class="modal-img" data-toggle="modal" data-target="#modal-show-img" src="${obj.photo}" style="object-fit:cover; width:100%; height:100%; cursor:pointer">
        </div>`,
        obj.location,
        active_status_badges[Number(obj.active_status)],
        `<div class="container-crud">
          <a href="#" data-toggle="modal" data-target="#modal-edit-kulkul" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit" 
            data-id="${obj.id}"
            data-id-desa="${obj.desa_adat.id}"
            data-code="${obj.code}"
            data-location="${obj.location}"
            data-active-status="${obj.active_status}"
            data-photo="${obj.photo}">
            <i class="mdi mdi-pencil"></i>
          </a>
          <a href="#" data-toggle="modal" data-target="#modal-hapus-kulkul" class="btn btn-inverse-danger btn-rounded btn-icon btn-action mr-2 btn-delete" title="Delete" data-id="${obj.id}">
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
    const maxCode = data.reduce(
      (res, cur) =>
        Number(cur.code) > Number(res) ? Number(cur.code) : Number(res),
      0
    );
    $("#tambah-kode").val(maxCode + 1);

    $("tbody").on("click", ".btn-edit", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      const id_desa = $(e.currentTarget).attr("data-id-desa");
      const code = $(e.currentTarget).attr("data-code");
      const active_status = $(e.currentTarget).attr("data-active-status");
      const location = $(e.currentTarget).attr("data-location");
      const photo = $(e.currentTarget).attr("data-photo");

      $("#edit-id").val(id);
      $("#edit-id-desa").val(id_desa);
      $("#edit-code").val(code);
      $("#edit-location").val(location);
      $("#edit-active-status").val(active_status).change();
      $("#edit-photo").attr("src", photo);
      $("#view-edit-photo").attr("src", photo);
    });

    $("tbody").on("click", ".btn-delete", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id);
    });
  }
};

$("tbody").on("click", ".modal-img", function () {
  const img_url = $(this).attr("src");
  $(".show-img").attr("src", img_url);
});
