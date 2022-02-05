$(document).ready(async () => {
  await readPecalang();
  await readMasyarakat();
});

let id_krama = [];

const readMasyarakat = async () => {
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/masyarakat/?id_desa=${idDesa}&active_status=true`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    data.map((obj) => {
      if (!id_krama.includes(obj.id)) {
        const option = `<option style="text-transform:capitalize;" value="${obj.id}">${obj.name}</option>`;
        $("#tambah-pecalang").append(option);
      }
    });
  } else {
    readMasyarakat();
  }
};

const active_status_badges = [
  "<label class='badge badge-secondary'>Nonaktif</label>",
  "<label class='badge badge-success'>Aktif</label>",
];

const sirine_authority_badges = [
  "<label class='badge badge-secondary'>Nonaktif</label>",
  "<label class='badge badge-primary'>Aktif</label>",
];

const work_status_badges = [
  "<label class='badge badge-secondary'>Tidak Bekerja</label>",
  "<label class='badge badge-info'>Bekerja</label>",
];

const readPecalang = async () => {
  startLoading();
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/pecalang/?id_desa=${idDesa}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    setupFilterDataTable(
      "tabel-pecalang",
      [6],
      data.map((obj, i) => [
        i + 1,
        obj.masyarakat.name,
        sirine_authority_badges[Number(obj.sirine_authority)],
        obj.prajuru_status ? "Prajuru" : "Pecalang",
        work_status_badges[Number(obj.working_status)],
        active_status_badges[Number(obj.active_status)],
        `<div class="container-crud">
          <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit Pecalang" data-toggle="modal"
            data-target="#modal-edit-pecalang" 
            data-id="${obj.id}"
            data-status-prajuru="${obj.prajuru_status}"
            data-masyarakat-id="${obj.masyarakat.id}"
            data-sirine-authority="${obj.sirine_authority}"
            data-active-status="${obj.active_status}">
            <i class="mdi mdi-pencil"></i>
          </a>
          <a href="profil-perangkat-desa.html?name=${obj.masyarakat.name}&avatar=${obj.masyarakat.avatar}&sirine-authority=${obj.sirine_authority}&working-status=${obj.working_status}&active-status=${obj.active_status}&category=${obj.masyarakat.category}&gender=${obj.masyarakat.gender}&birth=${obj.masyarakat.date_of_birth}&phone=${obj.masyarakat.phone}&banjar=${obj.masyarakat.banjar.name}&prajuru-status=${obj.prajuru_status}"
            class="btn btn-inverse-info btn-rounded btn-icon btn-action mr-2 btn-edit" title="Profil Krama">
            <i class="mdi mdi-account-card-details"></i>
          </a>
          <a href="#" class="btn btn-inverse-danger btn-rounded btn-icon btn-action mr-2 btn-delete" title="Hapus Pecalang" data-toggle="modal"
            data-target="#modal-hapus-pecalang" data-id="${obj.id}">
            <i class="mdi mdi-delete"></i>
          </a>
        </div>`,
      ])
    );

    data.map((obj) => {
      id_krama.push(obj.masyarakat.id);
    });

    stopLoading();
    $("tbody").on("click", ".btn-edit", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      const id_masyarakat = $(e.currentTarget).attr("data-masyarakat-id");
      const status_prajuru = $(e.currentTarget).attr("data-status-prajuru");
      const sirine_authority = $(e.currentTarget).attr("data-sirine-authority");
      const active_status = $(e.currentTarget).attr("data-active-status");

      $("#edit-id").val(id);
      $("#edit-id-masyarakat").val(id_masyarakat);
      $("#edit-sirine-authority").val(sirine_authority).change();
      $("#edit-active-status").val(active_status).change();
      $("#edit-status-prajuru").val(status_prajuru).change();
    });

    $("tbody").on("click", ".btn-delete", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id);
    });
  }
};
