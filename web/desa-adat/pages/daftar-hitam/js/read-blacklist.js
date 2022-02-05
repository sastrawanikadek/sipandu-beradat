$(document).ready(async () => {
  await readBlacklist();
});

const readBlacklist = async () => {
  const idDesa = localStorage.getItem("id_desa");
  const req = await fetch(
    `https://sipanduberadat.com/api/masyarakat/?id_desa=${idDesa}&block_status=true`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    setupFilterDataTable(
      "tabel-block",
      [4],
      data.map((obj, i) => [
        i + 1,
        obj.name,
        obj.gender ? "Laki-laki" : "Perempuan",
        obj.category,
        `<div class="container-crud">
          <button class="btn btn-primary btn-sm btn-block" data-toggle="modal" data-target="#modal-block"
            data-id="${obj.id}">
            <i class="mdi mdi-lock-open btn-icon-prepend"></i> Buka Blokir
         </button>
        </div>`,
      ])
    );

    $("tbody").on("click", ".btn-block", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#block-id").val(id);
    });
  }
};
