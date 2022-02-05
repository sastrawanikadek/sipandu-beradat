$(document).ready(async () => {
  await readInstansi();
  await readJenisPelaporan();
  await readOtoritasPelaporan();
});

const readInstansi = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/instansi-petugas/?active_status=true"
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    data.map((obj) => {
      const option = `<option value="${obj.id}">${obj.name}</option>`;
      $("#tambah-instansi").append(option);
      $("#edit-instansi").append(option);
    });
  }
};

const readJenisPelaporan = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/jenis-pelaporan/?active_status=true"
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    data.map((obj) => {
      const option = `<option value="${obj.id}">${obj.name}</option>`;
      $("#tambah-jenis-pelaporan").append(option);
      $("#edit-jenis-pelaporan").append(option);
    });
  }
};

const readOtoritasPelaporan = async () => {
  startLoading();
  const req = await fetch(
    "https://sipanduberadat.com/api/otoritas-pelaporan-instansi/"
  );
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    $(".table-datatable").DataTable({
      fixedHeader: {
        header: true,
        footer: true,
      },
      columnDefs: [{ orderable: false, targets: [3] }],
      data: data.map((obj, i) => [
        i + 1,
        obj.instansi_petugas.name,
        obj.jenis_pelaporan.name,
        `<div class="container-crud">
        <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit" data-toggle="modal"
        data-target="#modal-edit-otoritas-pelaporan" data-id="${obj.id}" data-id-instansi="${obj.instansi_petugas.id}" data-id-jenis-pelaporan="${obj.jenis_pelaporan.id}">
<i class="mdi mdi-pencil"></i>
        </a>
        <a href="#" class="btn btn-inverse-primary-red btn-rounded btn-icon btn-action mr-2 btn-delete" title="Delete" data-toggle="modal"
        data-target="#modal-hapus-otoritas-pelaporan" data-id="${obj.id}">
        <i class="mdi mdi-delete"></i>
        </a>
    </div>`,
      ]),
    });

    $("tbody").on("click", ".btn-edit", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      const id_instansi = $(e.currentTarget).attr("data-id-instansi");
      const id_jenis_pelaporan = $(e.currentTarget).attr(
        "data-id-jenis-pelaporan"
      );

      $("#edit-id").val(id);
      $("#edit-instansi").val(id_instansi);
      $("#edit-jenis-pelaporan").val(id_jenis_pelaporan);
    });
    stopLoading();
    $("tbody").on("click", ".btn-delete", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id);
    });
  }
};
