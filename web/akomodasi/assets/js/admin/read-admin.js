let id_admins = [];

$(document).ready(async () => {
  await readAdmin();
  readPegawai();
});

const readPegawai = async () => {
  const idAkomodasi = localStorage.getItem("id_akomodasi");
  const req = await fetch(
    `https://sipanduberadat.com/api/pegawai-akomodasi/?id_akomodasi=${idAkomodasi}&active_status=true`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    data.map((obj) => {
      if (!id_admins.includes(obj.id)) {
        const option = `<option value="${obj.id}">${obj.name}</option>`;
        $("#tambah-admin").append(option);
      }
    });
  } else {
    readPegawai();
  }
};

const active_status_badges = [
  "<label class='badge badge-secondary'>Tidak Aktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];

const superadmin_status_badges = [
  "<label class='badge badge-primary'>Admin</label>",
  "<label class='badge badge-primary-red'>Superadmin</label>",
];

const readAdmin = async () => {
  const idAkomodasi = localStorage.getItem("id_akomodasi");
  const req = await fetch(
    `https://sipanduberadat.com/api/admin-akomodasi/?id_akomdasi=${idAkomodasi}`
  );
  let { status_code, data } = await req.json();

  setupFilterDataTable(
    "table-admin",
    [9],
    data.map((obj, i) => [
      i + 1,
      obj.pegawai.name,
      obj.email,
      obj.pegawai.nik,
      obj.pegawai.date_of_birth,
      obj.pegawai.gender === "l" ? "Laki-laki" : "Perempuan",
      obj.pegawai.phone,
      active_status_badges[Number(obj.active_status)],
      superadmin_status_badges[Number(obj.super_admin_status)],
      `<div class="container-crud">
        <a href="#" class="btn btn-inverse-dark btn-rounded btn-icon btn-action mr-2 btn-forgot-password" title="Lupa Kata Sandi" data-toggle="modal"
            data-target="#modal-forgot-password-1" data-id="${obj.id}">
            <i class="mdi mdi-lock-alert"></i>
          </a>  
        <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit" data-toggle="modal"
            data-target="#modal-edit-admin"
            data-id="${obj.id}"
            data-id-pegawai="${obj.pegawai.id}"
            data-active-status="${obj.active_status}"
            data-email="${obj.email}">
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
  data.map((obj, i) => obj.active_status && id_admins.push(obj.pegawai.id));
};

$("tbody").on("click", ".btn-edit", (e) => {
  const id = $(e.currentTarget).attr("data-id");
  const id_pegawai = $(e.currentTarget).attr("data-id-pegawai");
  const active_status = $(e.currentTarget).attr("data-active-status");
  const email = $(e.currentTarget).attr("data-email");

  $("#edit-id").val(id);
  $("#edit-id-pegawai").val(id_pegawai);
  $("#edit-active-status").val(active_status).change();
  $("#edit-email").val(email);
});

$("tbody").on("click", ".btn-forgot-password", (e) => {
  const id = $(e.currentTarget).attr("data-id");
  $("#forgot-id").val(id);
});

$("tbody").on("click", ".btn-delete", (e) => {
  const id = $(e.currentTarget).attr("data-id");
  $("#hapus-id").val(id);
});
