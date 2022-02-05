const super_admin_status_badges = [
  "<label class='badge badge-primary'>Admin</label>",
  "<label class='badge badge-primary-red'>Superadmin</label>",
];
const active_status_badges = [
  "<label class='badge badge-primary-red'>Nonaktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];

$(document).ready(() => {
  readAdmin();
  readPetugas();
});

const readPetugas = async () => {
  const id_instansi = localStorage.getItem("id_instansi");
  const req = await fetch(
    `https://sipanduberadat.com/api/petugas/?id_instansi=${id_instansi}&active_status=true`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    data.map((obj) => {
      const option = `<option style="text-transform: capitalize" value="${obj.id}">${obj.name}</option>`;
      $("#tambah-admin-instansi").append(option);
    });
  } else {
    readPetugas();
  }
};

const readAdmin = async () => {
  startLoading();
  const id_instansi = localStorage.getItem("id_instansi");
  const req = await fetch(
    `https://sipanduberadat.com/api/admin-instansi/?id_instansi=${id_instansi}`
  );
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    setupFilterDataTable(
      "tabel-admin-instansi",
      [3, 6],
      data.map((obj, i) => [
        i + 1,
        obj.petugas.name,
        obj.petugas.gender === "l" ? "Laki-Laki" : "Perempuan",
        obj.petugas.instansi_petugas.name,
        super_admin_status_badges[Number(obj.super_admin_status)],
        active_status_badges[Number(obj.active_status)],
        `<div class="container-crud">
        <a href="#" class="btn btn-inverse-dark btn-rounded btn-icon btn-action mr-2 btn-forgot-password" title="Lupa Kata Sandi" data-toggle="modal"
          data-target="#modal-forgot-password-1" data-id="${obj.petugas.id}">
          <i class="mdi mdi-lock-alert"></i>
        </a>  
        <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit" data-toggle="modal"
          data-target="#modal-edit-admin" data-id="${obj.id}" data-id-petugas="${obj.petugas.id}" data-status="${obj.active_status}">
          <i class="mdi mdi-pencil"></i>
        </a>
        <a href="#" class="btn btn-inverse-primary-red btn-rounded btn-icon btn-action mr-2 btn-delete" title="Delete" data-toggle="modal"
        data-target="#modal-hapus-admin" data-id="${obj.id}">
        <i class="mdi mdi-delete"></i>
        </a>
    </div>`,
      ])
    );

    stopLoading();

    $("tbody").on("click", ".btn-edit", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      const id_petugas = $(e.currentTarget).attr("data-id-petugas");
      const active_status = $(e.currentTarget).attr("data-status");

      $("#edit-id").val(id);
      $("#edit-id-petugas").val(id_petugas);
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
