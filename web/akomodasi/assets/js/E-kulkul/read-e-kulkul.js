const active_status_badges = [
  "<label class='badge badge-primary-red'>Tidak Aktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];

$(document).ready(() => {
  read_kulkul();
  get_jenis_pelaporan();
});
$("#tambah-foto").change((e) => {
  if (e.currentTarget.files.length > 0) {
    $("#label-tambah-foto").text(e.currentTarget.files[0].name);
  } else {
    $("#label-tambah-foto").text("Select file");
  }
});

$("#edit-foto").change((e) => {
  if (e.currentTarget.files.length > 0) {
    $("#label-edit-foto").text(e.currentTarget.files[0].name);
  } else {
    $("#label-edit-foto").text("Select file");
  }
});

const read_kulkul = async () => {
  const idAkomodasi = localStorage.getItem("id_akomodasi");
  const fd = new FormData();
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    `https://sipanduberadat.com/api/sirine-akomodasi/?id_akomodasi=${idAkomodasi}`
  );
  const { status_code, data } = await req.json();

  setupFilterDataTable(
    "table-kulkul",
    [3, 5],
    data.map((obj, i) => [
      i + 1,
      obj.code,
      obj.location,
      `<img src="${obj.photo}" class="modal-img" style="height: 100px; width: 86px;" alt="">`,
      active_status_badges[Number(obj.active_status)],
      ` <div class="d-flex align-items-center">

          <button type="button" class="btn btn-action-kulkul btn-inverse-primary-red btn-rounded btn-icon btn-action mr-2"
          title="Aktifkan Kulkul" data-toggle="modal" data-target="#modal-action-kulkul"
          data-idDevice="${obj.code}">
          <i class="mdi mdi-bell-ring"></i>
          </button>
        
          <button type="button" class="btn btn-edit-kulkul btn-inverse-primary btn-rounded btn-icon btn-action mr-2"
          title="Edit" data-toggle="modal" data-target="#modal-edit-kulkul"
          data-id="${obj.id}"
          data-id-akomodasi="${obj.akomodasi.id}"
          data-kode="${obj.code}"
          data-alamat="${obj.location}"
          data-status-aktif="${obj.active_status}">
          <i class="mdi mdi-pencil"></i>
          </button>

          <button type="button" class="btn btn-inverse-danger btn-hapus-kulkul btn-rounded btn-icon btn-action mr-2"
          title="Hapus"  data-toggle="modal" data-target="#modal-hapus-kulkul" data-id="${obj.id}">
          <i class="mdi mdi-delete"></i>
          </button>
          </div>`,
    ])
  );
  stopLoading();
  const maxCode = data.reduce(
    (res, cur) =>
      Number(cur.code) > Number(res) ? Number(cur.code) : Number(res),
    0
  );
  $("#tambah-kode").val(maxCode + 1);
};

const get_jenis_pelaporan = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/jenis-pelaporan/?active_status=true"
  );
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    data.map((obj, i) => {
      const jenis_pelaporan = `
      <option value="${obj.id}">${obj.name}</option>
		`;
      $("#action-jenis-pelaporan").append(jenis_pelaporan);
    });
  }
};

$("tbody").on("click", ".btn-edit-kulkul", function () {
  const id = $(this).attr("data-id");
  const id_akomodasi = $(this).attr("data-id-akomodasi");
  const kode = $(this).attr("data-kode");
  const alamat = $(this).attr("data-alamat");
  const status_aktif = $(this).attr("data-status-aktif");

  $("#edit-id").val(id);
  $("#edit-id-akomodasi").val(id_akomodasi);
  $("#edit-kode").val(kode);
  $("#edit-alamat").val(alamat);
  $("#edit-status-aktif").val(status_aktif);
});

$("tbody").on("click", ".btn-action-kulkul", function () {
  const id = $(this).attr("data-idDevice");
  $("#action-id").val(id);
});

$("tbody").on("click", ".btn-hapus-kulkul", function () {
  const id = $(this).attr("data-id");
  $("#hapus-id").val(id);
});

$("tbody").on("click", ".modal-img", function () {
  $("#modal-show-img").modal("show");
  const img_url = $(this).attr("src");
  $(".show-img").attr("src", img_url);
});

