$(document).ready(async () => {
  await readKrama();
  await readStatusAktif();
  await readBanjar();
});

const readBanjar = async () => {
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/banjar/?id_desa=${idDesa}&active_status=true`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    data.map((obj) => {
      if (!obj.status) {
        const option = `<option value="${obj.id}">${obj.name}</option>`;
        $("#tambah-banjar").append(option);
      }
    });
  } else {
    readBanjar();
  }
};

const readStatusAktif = async () => {
  const req = await fetch(
    `https://sipanduberadat.com/api/status-aktif-masyarakat/`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    data.map((obj) => {
      if (!obj.status) {
        const option = `<option value="${obj.id}">${obj.name}</option>`;
        $("#edit-active-status-name").append(option);
      }
    });
  } else {
    readStatusAktif();
  }
};

const valid_status_badges = [
  "<label class='badge badge-secondary'>Belum Valid</label>",
  "<label class='badge badge-success'>Valid</label>",
];

const block_status_badges = [
  "<label class='badge badge-secondary'>Tidak Diblokir</label>",
  "<label class='badge badge-danger'>Diblokir</label>",
];

const readKrama = async () => {
  startLoading();
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/masyarakat/?id_desa=${idDesa}`
  );
  const { status_code, data } = await req.json();
  if (status_code === 200) {
    setupFilterDataTable(
      "tabel-krama",
      [12],
      data.map((obj, i) => [
        i + 1,
        obj.name,
        obj.email,
        obj.category,
        obj.phone,
        obj.date_of_birth,
        obj.nik,
        obj.gender === "l" ? "Laki-laki" : "Perempuan",
        obj.banjar.name,
        valid_status_badges[Number(obj.valid_status)],
        obj.active_status.status
          ? `<label class="badge badge-success">${obj.active_status.name}</label>`
          : `<label class="badge badge-secondary">${obj.active_status.name}</label>`,
        block_status_badges[Number(obj.block_status)],
        `<div class="container-crud">
          <a href="#" class="btn btn-inverse-success btn-rounded btn-icon btn-action mr-2 btn-valid" title="Validasi Krama" data-toggle="modal"
            data-target="#modal-valid-krama" 
            data-id="${obj.id}">
            <i class="mdi mdi-check"></i>
          </a>
          <a href="lacak-krama.html?id_krama=${obj.id}" class="btn btn-inverse-warning btn-rounded btn-icon btn-action mr-2 btn-valid" title="Lacak Krama">
            <i class="mdi mdi-map"></i>
          </a>
          <a href="profil-krama-desa-adat.html?name=${obj.name}&avatar=${obj.avatar}&valid-status=${obj.valid_status}&active-status=${obj.active_status.status}&block-status=${obj.block_status}&category=${obj.category}&gender=${obj.gender}&birth=${obj.date_of_birth}&phone=${obj.phone}&banjar=${obj.banjar.name}"
            class="btn btn-inverse-info btn-rounded btn-icon btn-action mr-2 btn-edit" title="Profil Krama">
            <i class="mdi mdi-account-card-details"></i>
          </a>
          <a href="#" class="btn btn-inverse-dark btn-rounded btn-icon btn-action mr-2 btn-history" title="Riwayat Blokir Krama Desa Adat" data-toggle="modal"
            data-target="#modal-history-blokir" 
            data-id="${obj.id}">
            <i class="mdi mdi-history"></i>
          </a>
          <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit Krama" data-toggle="modal"
            data-target="#modal-edit-krama" 
            data-id="${obj.id}"
            data-id-banjar="${obj.banjar.id}"
            data-name="${obj.name}"
            data-phone="${obj.phone}"
            data-date-of-birth="${obj.date_of_birth}"
            data-nik="${obj.nik}"
            data-jenis-kelamin="${obj.gender}"
            data-avatar="${obj.avatar}"
            data-jenis-krama="${obj.category}"
            data-status-valid="${obj.valid_status}"
            data-status-aktif="${obj.active_status.status}"
            data-email="${obj.email}">
            <i class="mdi mdi-pencil"></i>
          </a>
          <a href="#" class="btn btn-inverse-danger btn-rounded btn-icon btn-action mr-2 btn-delete" title="Hapus Krama" data-toggle="modal"
            data-target="#modal-hapus-krama" data-id="${obj.id}">
            <i class="mdi mdi-delete"></i>
          </a>
        </div>`,
      ])
    );

    stopLoading();

    $("tbody").on("click", ".btn-edit", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      const id_banjar = $(e.currentTarget).attr("data-id-banjar");
      const name = $(e.currentTarget).attr("data-name");
      const email = $(e.currentTarget).attr("data-email");
      const phone = $(e.currentTarget).attr("data-phone");
      const birth = $(e.currentTarget).attr("data-date-of-birth");
      const nik = $(e.currentTarget).attr("data-nik");
      const gender = $(e.currentTarget).attr("data-jenis-kelamin");
      const avatar = $(e.currentTarget).attr("data-avatar");
      const category = $(e.currentTarget).attr("data-jenis-krama");
      const valid_status = $(e.currentTarget).attr("data-status-valid");
      const active_status = $(e.currentTarget).attr("data-status-aktif");

      $("#edit-id").val(id);
      $("#edit-id-banjar").val(id_banjar);
      $("#edit-name").val(name);
      $("#edit-email").val(email);
      $("#edit-phone").val(phone);
      $("#edit-birth").val(birth);
      $("#edit-nik").val(nik);
      $("#edit-gender").val(gender);
      $("#view-edit-avatar").attr("src", avatar);
      $("#edit-category").val(category).change();
      $("#edit-valid-status").val(valid_status).change();
      $("#edit-active-status").val(active_status).change();
    });

    $("tbody").on("click", ".btn-delete", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id);
    });

    $("tbody").on("click", ".btn-history", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      readHistory(id);
    });

    $("tbody").on("click", ".btn-valid", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#valid-id").val(id);
    });
  }
};

const readHistory = async (id) => {
  const req = await fetch(
    `https://sipanduberadat.com/api/masyarakat/find-unblock-history/?id_masyarakat=${id}`
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
