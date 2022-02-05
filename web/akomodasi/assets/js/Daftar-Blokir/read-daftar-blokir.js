$(document).ready(() => {
  readBlokir();
});

const readBlokir = async () => {
  const idAkomodasi = localStorage.getItem("id_akomodasi");
  const req = await fetch(
    `https://sipanduberadat.com/api/tamu/?block_status=true&id_akomodasi=${idAkomodasi}`
  );
  const { status_code, data, message } = await req.json();

  setupFilterDataTable(
    "table-blokir",
    [10],
    data.map((obj, i) => [
      i + 1,
      obj.name,
      obj.identity_type,
      obj.identity_number,
      obj.negara.name,
      obj.gender == "l" ? "Laki-Laki" : "Perempuan",
      obj.phone,
      obj.date_of_birth,
      obj.check_in.start_time,
      obj.check_in.end_time,
      ` <button type="button" class="btn btn-primary mr-5 btn-buka-blokir"
            title="Buka Blokir" data-toggle="modal" data-target="#modal-buka-blokir" data-id="${obj.id}"">
          Buka Blokir
          </button>`,
    ])
  );
  stopLoading();
};

$(".btn-buka-blokir").click((e) => {
  const id = $(e.currentTarget).attr("data-id");

  $("#data-id").val(id);
});
