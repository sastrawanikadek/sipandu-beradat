const active_status_badges = [
  "<label class='badge badge-primary-red'>Nonaktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];

const blockir_status_badges = [
  "<label class='badge badge-success'>Tidak Diblokir</label>",
  "<label class='badge badge-primary-red'>Diblokir</label>",
];

const valid_status_badges = [
  "<label class='badge badge-secondary'>Belum Valid</label>",
  "<label class='badge badge-success'>Valid</label>",
];

$(document).ready(async () => {
  const negaras = await read_negara();
  read_tamu();

  negaras.map((obj) => {
    const option = `<option value="${obj.id}">${obj.name}</option>`;
    $("#tambah-negara").append(option);
    $("#edit-negara").append(option);
  });
});

const read_negara = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/negara/?active_status=true"
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    read_negara();
  }
};

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

const read_tamu = async () => {
  const idAkomodasi = localStorage.getItem("id_akomodasi");
  const req = await fetch(
    `https://sipanduberadat.com/api/tamu/?id_akomodasi=${idAkomodasi}`
  );
  const { data } = await req.json();

  setupFilterDataTable(
    "table-tamu",
    [14],
    data.map((obj, i) => [
      i + 1,
      obj.name,
      obj.email,
      obj.identity_type,
      obj.identity_number,
      obj.negara.name,
      obj.gender == "l" ? "Laki-Laki" : "Perempuan",
      obj.phone,
      obj.date_of_birth,
      obj.check_in.start_time,
      obj.check_in.end_time,
      valid_status_badges[Number(obj.valid_status)],
      blockir_status_badges[Number(obj.block_status)],
      active_status_badges[Number(obj.active_status)],
      `  <div class="d-flex align-items-center">
            <a 
            href="detail-wisatawan.html?title=${obj.title}&name=${
        obj.name
      }&email=${obj.email}&identity-type=${obj.identity_type}&identity-number=${
        obj.identity_number
      }&negara=${obj.negara.name}&gender=${
        obj.gender === "l" ? "Laki-laki" : "Perempuan"
      }&phone=${obj.phone}&date-of-birth=${obj.date_of_birth}&check-in=${
        obj.check_in.start_time
      }&check-out=${obj.check_in.end_time}&block-status=${
        obj.block_status
      }&active-status=${obj.active_status}&valid-status=${
        obj.valid_status
      }&avatar=${obj.avatar}"
            class="btn btn-inverse-info btn-rounded btn-icon btn-action mr-2 btn-edit" title="Profil Wisatawan">
            <i class="mdi mdi-account-card-details"></i>
          </a>
          <a href="#" class="btn btn-inverse-success btn-rounded btn-icon btn-action mr-2 btn-valid" title="Validasi Wisatawan" data-toggle="modal"
            data-target="#modal-valid-wisatawan" 
            data-id="${obj.id}">
            <i class="mdi mdi-check"></i>
          </a>
          <a href="riwayat-lokasi-wisatawan.html?id_tamu=${
            obj.id
          }" class="btn btn-inverse-warning btn-rounded btn-icon btn-action mr-2 btn-valid" title="Lacak Wisatawan">
            <i class="mdi mdi-map"></i>
          </a>
          <a href="#" class="btn btn-inverse-dark btn-rounded btn-icon btn-action mr-2 btn-history" title="Riwayat Blokir Wisatawan " data-toggle="modal"
            data-target="#modal-history-blokir" 
            data-id="${obj.id}">
            <i class="mdi mdi-history"></i>
          </a>

            <button type="button" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit-tamu"
              title="Edit" data-toggle="modal" data-target="#modal-edit-wisatawan" 
              data-id="${obj.id}"
              data-id-akomodasi="${obj.akomodasi.id}"
              data-id-negara="${obj.negara.id}"
              data-name="${obj.name}"
              data-email="${obj.email}"
              data-phone="${obj.phone}"
              data-check-in="${obj.check_in.start_time}"
              data-check-out="${obj.check_in.end_time}"
              data-birthday="${obj.date_of_birth}"
              data-identity-type="${
                obj.identity_type === "Identity Card"
                  ? 0
                  : obj.identity_type === "Driving License"
                  ? 1
                  : 2
              }"
              data-identity-number="${obj.identity_number}"
              data-gender="${obj.gender}"
              data-avatar="${obj.avatar}"
              data-status-valid="${Number(obj.valid_status)}"
              data-status-aktif="${Number(obj.active_status)}">
              <i class="mdi mdi-pencil"></i>
            </button>

            <button type="button" class="btn btn-inverse-danger btn-rounded btn-icon btn-action mr-2 btn-hapus-tamu"
              title="Hapus"  data-toggle="modal" data-target="#modal-hapus-wisatawan" data-id="${
                obj.id
              }">
              <i class="mdi mdi-delete"></i>
            </button>
          </div>`,
    ])
  );
  stopLoading();
};

$("tbody").on("click", ".btn-edit-tamu", function () {
  const id = $(this).attr("data-id");
  const id_akomodasi = $(this).attr("data-id-akomodasi");
  const id_negara = $(this).attr("data-id-negara");
  const name = $(this).attr("data-name");
  const email = $(this).attr("data-email");
  const phone = $(this).attr("data-phone");
  const birthday = $(this).attr("data-birthday");
  const identity_type = $(this).attr("data-identity-type");
  const identity_number = $(this).attr("data-identity-number");
  const gender = $(this).attr("data-gender");
  const avatar = $(this).attr("data-avatar");
  const valid_status = $(this).attr("data-status-valid");
  const active_status = $(this).attr("data-status-aktif");
  const check_in = new Date($(this).attr("data-check-in"));
  const check_out = new Date($(this).attr("data-check-out"));

  $("#edit-id").val(id);
  $("#edit-akomodasi").val(id_akomodasi);
  $("#edit-negara").val(id_negara);
  $("#edit-name").val(name);
  $("#edit-email").val(email);
  $("#edit-phone").val(phone);
  $("#edit-birthday").val(birthday);
  $("#edit-identity-type").val(identity_type);
  $("#edit-identity-number").val(identity_number);
  $("#edit-gender").val(gender);
  $("#edit-valid-status").val(valid_status).change();
  $("#edit-status-aktif").val(active_status);
  $("#edit-avatar").attr("src", avatar);
  $("#edit-check-in").val(
    `${check_in.getFullYear()}-${(check_in.getMonth() + 1)
      .toString()
      .padStart(2, "0")}-${check_in
      .getDate()
      .toString()
      .padStart(2, "0")}T${check_in
      .getHours()
      .toString()
      .padStart(2, "0")}:${check_in
      .getMinutes()
      .toString()
      .padStart(2, "0")}:${check_in.getSeconds().toString().padStart(2, "0")}`
  );
  $("#edit-check-out").val(
    `${check_out.getFullYear()}-${(check_out.getMonth() + 1)
      .toString()
      .padStart(2, "0")}-${check_out
      .getDate()
      .toString()
      .padStart(2, "0")}T${check_out
      .getHours()
      .toString()
      .padStart(2, "0")}:${check_out
      .getMinutes()
      .toString()
      .padStart(2, "0")}:${check_out.getSeconds().toString().padStart(2, "0")}`
  );
});

$("tbody").on("click", ".btn-hapus-tamu", function () {
  const id = $(this).attr("data-id");
  $("#hapus-id").val(id);
});

$("tbody").on("click", ".btn-history", function () {
  const id = $(this).attr("data-id");
  readHistory(id);
});

$("tbody").on("click", ".btn-valid", function () {
  const id = $(this).attr("data-id");
  $("#valid-id").val(id);
});

const readHistory = async (id) => {
  const req = await fetch(
    `https://sipanduberadat.com/api/tamu/find-unblock-history/?id_tamu=${id}`
  );
  const { status_code, data } = await req.json();
  if (status_code === 200) {
    $("#riwayat-blokir").html("");
    if (data.histories.length !== 0) {
      data.histories.map((obj, i) => {
        $("#riwayat-blokir").append(
          `<li class="list-group-item">${obj.time}</li>`
        );
      });
    } else {
      $("#riwayat-blokir").append(
        `<li class="list-group-item">Tidak Terdapat Riwayat Blokir</li>`
      );
    }
  }
};
