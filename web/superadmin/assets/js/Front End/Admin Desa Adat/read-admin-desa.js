$(document).ready(() => {
  read_admin();
  read_krama();
});

const read_krama = async () => {
  const idDesa = localStorage.getItem("id_desa")
  const req = await fetch(`https://api-sipandu-beradat.000webhostapp.com/masyarakat/?id_desa=${idDesa}&active_status=true`);
  const {
    status_code,
    data
  } = await req.json();

  if (status_code === 200) {
    data.map(obj => {
      const option = `<option value="${obj.id}">${obj.name}</option>`;
      $("#tambah-admin").append(option)
    })
  } else {
    read_krama()
  }
}

const read_admin = async () => {
  const idDesa = localStorage.getItem("id_desa")
  const req = await fetch(`https://api-sipandu-beradat.000webhostapp.com/admin-desa-adat/?id_desa=${idDesa}`);
  const {
    status_code,
    data
  } = await req.json();

  if (status_code === 200) {
    $("#tabel-admin-desa tbody").html("")
    data.map((obj, i) => {
      const row = `
				  <tr>
	          <td>${i + 1}</td>
						<td>${obj.masyarakat.name}</td>
						<td>${obj.masyarakat.category}</td>
						<td>${obj.masyarakat.phone}</td>
						<td>${obj.masyarakat.date_of_birth}</td>
						<td>${obj.masyarakat.nik}</td>
						<td>${obj.masyarakat.gender}</td>
						<td>${obj.masyarakat.banjar.name}</td>
						${obj.active_status ? '<td><div class="badge badge-success">Aktif</div></td>' : '<td><div class="badge badge-secondary">Tidak Aktif</div></td>'}
						${obj.super_admin_status ? '<td><div class="badge badge-primary">Aktif</div></td>' : '<td><div class="badge badge-secondary">Tidak Aktif</div></td>'}
            <td>
              <div class="container-crud d-flex">
              <a href="#" class="btn btn-sm btn-icon btn-primary btn-edit-admin mr-2" title="Edit Admin Desa Adat"
                data-toggle="modal" data-target="#modal-edit-admin"
                data-id="${obj.id}" 
                data-id-masyarakat="${obj.masyarakat.id}"
                data-active-status="${Number(obj.active_status)}">
                <i class="fas fa-pencil-alt"></i>
              </a>
              <a href="#" class="btn btn-icon btn-sm btn-danger btn-hapus-admin" title="Hapus Admin Desa Adat"
                data-toggle="modal" data-target="#modal-hapus-admin" 
                data-id="${obj.id}">
                <i class="fas fa-trash"></i>
              </a>
              </div>
            </td>
          </tr>
			 `;
      $("#tabel-admin-desa tbody").append(row);
    });

    $(".btn-edit-admin").click(e => {
      const id = $(e.currentTarget).attr("data-id");
      const id_masyarakat = $(e.currentTarget).attr("data-id-masyarakat");
      const active_status = $(e.currentTarget).attr("data-active-status");

      $("#edit-id").val(id)
      $("#edit-id-masyarakat").val(id_masyarakat)
      $("#edit-active-status").val(active_status)
    })

    $(".btn-hapus-admin").click(e => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id)
    })
  }
};
