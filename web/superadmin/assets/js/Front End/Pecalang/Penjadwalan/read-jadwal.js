$(document).ready(() => {
  read_jadwal();
});

const read_jadwal = async () => {
  const idDesa = localStorage.getItem("id_desa")
  const req = await fetch(`https://api-sipandu-beradat.000webhostapp.com/jadwal-pecalang/?id_desa=${idDesa}`);
  const {
    status_code,
    data
  } = await req.json();

  if (status_code === 200) {
    $("#tabel-jadwal-pecalang tbody").html("")
    data.map((obj, i) => {
      const option = `<option value="${obj.pecalang.id}">${obj.pecalang.masyarakat.name}</option>`;
      $("#tambah-id-pecalang").append(option)
      const row = `
				  <tr>
						<td>${i + 1}</td>
						<td>${obj.pecalang.masyarakat.name}</td>
            <td>${obj.days.map(v => v.name).join(", ")}</td>
           	<td>
						  <div class="d-flex">
							 <a href="#" title="Edit Penjadwalan Pecalang" class="btn btn-sm btn-icon btn-primary btn-edit-jadwal mr-2"
								data-toggle="modal" data-target="#modal-edit-jadwal"
								data-id-pecalang="${obj.pecalang.id}"
								data-day="${obj.days.id}">
								<i class="fas fa-pencil-alt"></i>
							 </a>
								<a href="#" class="btn btn-icon btn-sm btn-danger btn-hapus-jadwal" title="Hapus Jadwal Pecalang"
								data-toggle="modal" data-target="#modal-hapus-jadwal"
								data-id="${obj.pecalang.id}">
								<i class="fas fa-trash"></i>
							</a>
						  </div>
						</td>
				  </tr>
			 `;
      $("#tabel-jadwal-pecalang tbody").append(row);
    });

    $(".btn-edit-jadwal").click(e => {
      const id_pecalang = $(e.currentTarget).attr("data-id-pecalang");
      const days = $(e.currentTarget).attr("data-day");

      $("#edit-id").val(id)
      $("#edit-id-pecalang").val(id_pecalang)
      $("#edit-day").attr("checked", days)
    })

    $(".btn-hapus-jadwal").click(e => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id)
    })

  }
};
