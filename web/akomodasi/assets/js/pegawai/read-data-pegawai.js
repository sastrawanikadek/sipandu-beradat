$(document).ready(async () => {
  await read_pegawai();
});

const active_status_badges = [
  "<label class='badge badge-primary-red'>Nonaktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];
$("#tambah-avatar").change((e) => {
  if (e.currentTarget.files.length > 0) {
    $("#label-tambah-avatar").text(e.currentTarget.files[0].name);
  } else {
    $("#label-tambah-avatar").text("Select file");
  }
});

$("#edit-avatar").change((e) => {
  if (e.currentTarget.files.length > 0) {
    $("#label-edit-avatar").text(e.currentTarget.files[0].name);
  } else {
    $("#label-edit-avatar").text("Select file");
  }
});

const read_pegawai = async () => {
  const idAkomodasi = localStorage.getItem("id_akomodasi");
  const req = await fetch(
    `https://sipanduberadat.com/api/pegawai-akomodasi/?id_akomodasi=${idAkomodasi}`
  );
  let { data } = await req.json();

  setupFilterDataTable(
    "table-pegawai",
    [7],
    data.map((obj, i) => [
      i + 1,
      obj.name,
      obj.nik,
      obj.gender == "l" ? "Laki-Laki" : "Perempuan",
      obj.phone,
      obj.date_of_birth,
      active_status_badges[Number(obj.active_status)],

      `<div class="container-crud">

        <a 
        href="detail-pegawai.html?title=${obj.title}&name=${
        obj.name
      }&active-status=${obj.active_status}&akomodasi=${
        obj.akomodasi.name
      }&nik=${obj.nik}&gender=${
        obj.gender === "l" ? "Laki-laki" : "Perempuan"
      }&phone=${obj.phone}&date-of-birth=${obj.date_of_birth}&avatar=${
        obj.avatar
      }"
        class="btn btn-inverse-info btn-rounded btn-icon btn-action mr-2 btn-edit" title="Profil Pegawai">
        <i class="mdi mdi-account-card-details"></i>
      </a>

          <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit" data-toggle="modal"
            data-target="#modal-edit-pegawai" 
            data-id="${obj.id}"
            data-id-akomodasi="${obj.akomodasi.id}"
            data-name="${obj.name}"
            data-nik="${obj.nik}"
            data-gender="${obj.gender}"
            data-phone="${obj.phone}"
            data-date-of-birth="${obj.date_of_birth}"
            data-avatar="${obj.avatar}"
            data-active-status="${obj.active_status}">
            <i class="mdi mdi-pencil"></i>
          </a>
          <a href="#" class="btn btn-inverse-danger btn-rounded btn-icon btn-action mr-2 btn-delete" title="Delete" data-toggle="modal"
            data-target="#modal-hapus-pegawai" data-id="${obj.id}">
            <i class="mdi mdi-delete"></i>
          </a>
        </div>`,
    ])
  );
  stopLoading();
};

$("tbody").on("click", ".btn-edit", (e) => {
  const id = $(e.currentTarget).attr("data-id");
  const id_akomodasi = $(e.currentTarget).attr("data-id-akomodasi");
  const name = $(e.currentTarget).attr("data-name");
  const nik = $(e.currentTarget).attr("data-nik");
  const gender = $(e.currentTarget).attr("data-gender");
  const phone = $(e.currentTarget).attr("data-phone");
  const date_of_birth = $(e.currentTarget).attr("data-date-of-birth");
  const avatar = $(e.currentTarget).attr("data-avatar");
  const active_status = $(e.currentTarget).attr("data-active-status");

  $("#edit-id").val(id);
  $("#edit-akomodasi").val(id_akomodasi);
  $("#edit-name").val(name);
  $("#edit-nik").val(nik);
  $("#edit-gender").val(gender);
  $("#edit-phone").val(phone);
  $("#edit-date-of-birth").val(date_of_birth);
  $("#edit-avatar").attr("src", avatar);
  $("#edit-active-status").val(active_status);
});

$("tbody").on("click", ".btn-delete", (e) => {
  const id = $(e.currentTarget).attr("data-id");
  $("#hapus-id").val(id);
});
