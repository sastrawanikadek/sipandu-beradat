$(document).ready(async () => {
  await readAdmin();
  readKrama();
});

const readKrama = async () => {
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/masyarakat/?id_desa=${idDesa}&active_status=true`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    data.map((obj) => {
      const option = `<option style="text-transform: capitalize;" value="${obj.id}">${obj.name}</option>`;
      $("#tambah-admin").append(option);
    });
  } else {
    readKrama();
  }
};

const active_status_badges = [
  "<label class='badge badge-secondary'>Nonaktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];

const superadmin_status_badges = [
  "<label class='badge badge-primary'>Admin</label>",
  "<label class='badge badge-primary-red'>Superadmin</label>",
];

const readAdmin = async () => {
  startLoading();
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/admin-desa-adat/?id_desa=${idDesa}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    setupFilterDataTable(
      "tabel-admin",
      [10],
      data.map((obj, i) => [
        i + 1,
        obj.masyarakat.name,
        obj.masyarakat.category,
        obj.masyarakat.phone,
        obj.masyarakat.date_of_birth,
        obj.masyarakat.nik,
        obj.masyarakat.gender === "l" ? "Laki-laki" : "Perempuan",
        obj.masyarakat.banjar.name,
        active_status_badges[Number(obj.active_status)],
        superadmin_status_badges[Number(obj.super_admin_status)],
        `<div class="container-crud">
          <a href="#" class="btn btn-inverse-dark btn-rounded btn-icon btn-action mr-2 btn-forgot-password" title="Lupa Kata Sandi" data-toggle="modal"
          data-target="#modal-forgot-password-1" data-id="${obj.masyarakat.id}">
          <i class="mdi mdi-lock-alert"></i>
        </a>  
          <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit" data-toggle="modal"
            data-target="#modal-edit-admin"
            data-id="${obj.id}"
            data-id-masyarakat="${obj.masyarakat.id}"
            data-active-status="${obj.active_status}">
            <i class="mdi mdi-pencil"></i>
          </a>
          <a href="#" class="btn btn-inverse-danger btn-rounded btn-icon btn-action mr-2 btn-delete" title="Delete" data-toggle="modal"
            data-target="#modal-hapus-admin" data-id="${obj.id}">
            <i class="mdi mdi-delete"></i>
          </a>
        </div>`,
      ])
    );

    stopLoading();
    $("tbody").on("click", ".btn-edit", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      const id_masyarakat = $(e.currentTarget).attr("data-id-masyarakat");
      const active_status = $(e.currentTarget).attr("data-active-status");

      $("#edit-id").val(id);
      $("#edit-id-masyarakat").val(id_masyarakat);
      $("#edit-active-status").val(active_status).change();
    });

    $("tbody").on("click", ".btn-delete", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id);
    });

    $("tbody").on("click", ".btn-forgot-password", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#forgot-id").val(id);
    });
  }
};
