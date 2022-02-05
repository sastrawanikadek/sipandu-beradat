$(document).ready(async () => {
  await readJadwalPecalang();
  await readPecalang();
});

let id_krama = [];
let id_jadwal = [];
const readPecalang = async () => {
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/pecalang/?id_desa=${idDesa}&active_status=true`
  );
  const { status_code, data } = await req.json();
  if (status_code === 200) {
    data.map((obj) => {
      const option = `<option style="text-transform:capitalize;" value="${obj.id}">${obj.masyarakat.name}</option>`;
      $("#tambah-pecalang").append(option);
    });
    if ($("#tambah-pecalang").children().length === 0) {
      $("#tambah-pecalang").append(`<option disabled>Tidak ada data</option>`);
    }
  } else {
    readPecalang();
  }
};

const checkDate = (dates) => {
  const currentDate = new Date();
  let currentYear = currentDate.getFullYear();
  let currentMonth = currentDate.getMonth() + 1;
  let currentDateday = currentDate.getDate();
  if (currentMonth < 10) {
    currentMonth = "0" + currentMonth;
  }
  if (currentDateday < 10) {
    currentDateday = "0" + currentDateday;
  }
  const currentStringDate = `${currentYear}-${currentMonth}-${currentDateday}`;
  return dates.includes(currentStringDate);
};

const readJadwalPecalang = async () => {
  startLoading();
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/jadwal-pecalang/?id_desa=${idDesa}&active_status=true`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    setupFilterDataTable(
      "tabel-jadwal",
      [3],
      data.map((obj, i) => [
        i + 1,
        obj.pecalang.masyarakat.name,
        obj.days.length == 0
          ? "Tidak ada jadwal"
          : checkDate(obj.days)
          ? "Bertugas"
          : "Libur",
        `<div class="container-crud">
            <a href="#" class="btn btn-inverse-primary btn-rounded btn-icon btn-action mr-2 btn-edit" title="Edit Pecalang" data-toggle="modal"
              data-target="#modal-edit-krama"
              data-id-pecalang="${obj.pecalang.id}"
              data-nama-pecalang="${obj.pecalang.masyarakat.name}"
              data-days="${obj.days}">
              <i class="mdi mdi-pencil"></i>
            </a>
            <a href="#" class="btn btn-inverse-danger btn-rounded btn-icon btn-action mr-2 btn-delete" title="Hapus Pecalang" data-toggle="modal"
              data-target="#modal-hapus-jadwal" data-id-pecalang="${obj.pecalang.id}">
              <i class="mdi mdi-delete"></i>
            </a>
          </div>`,
      ])
    );

    stopLoading();
    data.map((obj, i) => {
      id_krama.push(obj.pecalang.id);
    });

    $("tbody").on("click", ".btn-edit", (e) => {
      const id_pecalang = $(e.currentTarget).attr("data-id-pecalang");

      const nama_pecalang = $(e.currentTarget).attr("data-nama-pecalang");
      const days = $(e.currentTarget).attr("data-days");
      $("#edit-id-jadwal-pecalang").val(id_pecalang);
      $("#edit-nama-jadwal-pecalang").val(nama_pecalang);
      $("#edit-date").datepicker(
        "setDates",
        days.split(",").map((v) => new Date(v))
      );
    });

    $("tbody").on("click", ".btn-delete", (e) => {
      const id_pecalang = $(e.currentTarget).attr("data-id-pecalang");
      $("#hapus-id").val(id_pecalang);
    });
  }
};
