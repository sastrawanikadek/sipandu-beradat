const active_status_badges = [
  "<label class='badge badge-primary-red'>Nonaktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];

$(document).ready(() => {
  readPetugas();
});

const readPetugas = async () => {
  startLoading();
  const idInstansi = localStorage.getItem("id_instansi");
  const req = await fetch(
    `https://sipanduberadat.com/api/petugas/?id_instansi=${idInstansi}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    setupFilterDataTable(
      "tabel-petugas",
      [7],
      data.map((obj, i) => [
        i + 1,
        obj.name,
        obj.email,
        obj.date_of_birth,
        obj.gender === "l" ? "Laki-Laki" : "Perempuan",
        obj.phone,
        obj.nik,
        active_status_badges[Number(obj.active_status)],
        `<div class="d-flex justify-content-center" style="gap:14px;">
          <a href="detail-petugas.html?view-name=${obj.name}&view-avatar=${
          obj.avatar
        }&view-active-status=${obj.active_status}&nik=${obj.nik}&gender=${
          obj.gender === "l" ? "Laki-Laki" : "Perempuan"
        }&phone=${obj.phone}&birth=${obj.date_of_birth}&kecamatan=${
          obj.instansi_petugas.kecamatan.name
        }" 
          class="btn btn-inverse-success btn-rounded btn-icon btn-action mr-2 btn-detail" title="Detail Petugas" >
          <i class="mdi mdi-account-details"></i>
          </a>
          <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit" data-toggle="modal"
          data-target="#modal-edit-petugas"
            data-id="${obj.id}"
            data-id-instansi="${obj.instansi_petugas.id}"
            data-name="${obj.name}"
            data-phone="${obj.phone}"
            data-birthday="${obj.date_of_birth}"
            data-nik="${obj.nik}"
            data-gender="${obj.gender}"
            data-active-status="${obj.active_status}"
            data-avatar="${obj.avatar}"
            data-email="${obj.email}">
           
            <i class="mdi mdi-pencil"></i>
          </a>
          <a href="#" class="btn btn-inverse-primary-red btn-rounded btn-icon btn-action mr-2 btn-delete" title="Delete" data-toggle="modal"
              data-target="#modal-hapus-petugas"
            data-id="${obj.id}">
            <i class="mdi mdi-delete"></i>
          </a>
        </div>`,
      ])
    );

    stopLoading();

    $("tbody").on("click", ".btn-edit", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      const id_instansi = $(e.currentTarget).attr("data-id-instansi");
      const name = $(e.currentTarget).attr("data-name");
      const email = $(e.currentTarget).attr("data-email");
      const phone = $(e.currentTarget).attr("data-phone");
      const birth = $(e.currentTarget).attr("data-birthday");
      const nik = $(e.currentTarget).attr("data-nik");
      const gender = $(e.currentTarget).attr("data-gender");
      const active_status = $(e.currentTarget).attr("data-active-status");
      const avatar = $(e.currentTarget).attr("data-avatar");

      $("#edit-id").val(id);
      $("#edit-id-instansi").val(id_instansi);
      $("#edit-nama").val(name);
      $("#edit-email").val(email);
      $("#edit-telp").val(phone);
      $("#edit-tgl-lahir").val(birth);
      $("#edit-nik").val(nik);
      $("#edit-jenis-kelamin").val(gender);
      $("#edit-profil-pic").attr("src", avatar);
      $("#view-edit-avatar").attr("src", avatar);
      $("#edit-active-status").val(active_status).change();
    });

    $("tbody").on("click", ".btn-delete", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id);
    });
  }
};
