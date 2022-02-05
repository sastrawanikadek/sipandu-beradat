$(document).ready(() => {
  read_pecalang();
  read_krama();
});

const read_krama = async () => {
  const idDesa = localStorage.getItem("id_desa")
  const req = await fetch(`https://api-sipandu-beradat.000webhostapp.com/masyarakat/?id_desa=${idDesa}`);
  const {
    status_code,
    data
  } = await req.json();
  if (status_code === 200) {
    data.map(obj => {
      const option = `<option value="${obj.id}">${obj.name}</option>`;
      $("#tambah-nama-pecalang").append(option)
    })
  } else {
    read_krama()
  }
}

const read_pecalang = async () => {
  const idDesa = localStorage.getItem("id_desa")
  const req = await fetch(`https://api-sipandu-beradat.000webhostapp.com/pecalang/?id_desa=${idDesa}`);
  const {
    status_code,
    data,
    message
  } = await req.json();

  if (status_code === 200) {
    $("#tabel-pecalang tbody").html("")
    data.map((obj, i) => {
      const row = `
				  <tr>
						<td>${i + 1}</td>
						<td>${obj.masyarakat.name}</td>
            ${obj.sirine_authority ? '<td><div class="badge badge-primary">Iya</div></td>' : '<td><div class="badge badge-secondary">Tidak</div></td>'}
            ${obj.working_status ? '<td><div class="badge badge-primary">Bertugas</div></td>' : '<td><div class="badge badge-secondary">Tidak Bertugas</div></td>'}
						${obj.active_status ? '<td><div class="badge badge-success">Aktif</div></td>' : '<td><div class="badge badge-secondary">Tidak Aktif</div></td>'}
           	<td>
						  <div class="d-flex">
							 <a href="#" title="Edit Data Pecalang" class="btn btn-sm btn-icon btn-primary btn-edit-pecalang mr-2"
								data-toggle="modal" data-target="#modal-edit-pecalang" 
								data-id="${obj.id}"
								data-id-masyarakat="${obj.masyarakat.id}"
								data-otoritas-sirine="${obj.sirine_authority}"
                data-status-bertugas="${Number(obj.working_status)}"
								data-status-aktif="${Number(obj.active_status)}">
								<i class="fas fa-pencil-alt"></i>
							 </a>
								<a href="#" class="btn btn-icon btn-sm btn-danger btn-hapus-pecalang" title="Hapus Krama Desa Adat"
								data-toggle="modal" data-target="#modal-hapus-pecalang"
								data-id="${obj.id}">
								<i class="fas fa-trash"></i>
							</a>
						  </div>
						</td>
				  </tr>
			 `;
      $("#tabel-pecalang tbody").append(row);
    });

    $(".btn-edit-pecalang").click(e => {
      const id = $(e.currentTarget).attr("data-id");
      const id_masyarakat = $(e.currentTarget).attr("data-id-masyarakat");
      const sirine_authority = $(e.currentTarget).attr("data-otoritas-sirine");
      const active_status = $(e.currentTarget).attr("data-status-aktif");

      $("#edit-id").val(id)
      $("#edit-id-masyarakat").val(id_masyarakat)
      $("#edit-otoritas-sirine").attr("checked", sirine_authority)
      $("#edit-status-aktif").val(active_status)
    })

    $(".btn-hapus-pecalang").click(e => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id)
    })

  }
};
