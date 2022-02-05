$(document).ready(() => {
  read_kulkul();
});

const read_kulkul = async () => {
  const idDesa = localStorage.getItem("id_desa")
  const fd = new FormData();
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(`https://api-sipandu-beradat.000webhostapp.com/sirine-desa/?id_desa=${idDesa}`);
  const {
    status_code,
    data,
    message
  } = await req.json();

  if (status_code === 200) {
    $("#tabel-kulkul tbody").html("");
    data.map((obj, i) => {
      const row = `
					 <tr>
						  <td>${i + 1}</td>
						  <td>${obj.code}</td>
						  <td style="text-transform: capitalize;">${obj.location}</td>
						  ${obj.active_status ? '<td><div class="badge badge-success">Aktif</div></td>' : '<td><div class="badge badge-secondary">Tidak Aktif</div></td>'}
						  <td>
							 <div class="d-flex">
								 <a href="#" class="btn btn-sm btn-icon btn-primary btn-edit-kulkul mr-2" title="Edit E-Kulkul"
									 data-toggle="modal" data-target="#modal-edit-kulkul"
									 data-id="${obj.id}"
                   data-id-desa="${obj.desa_adat.id}"
									 data-kode="${obj.code}"
									 data-alamat="${obj.location}"
									 data-status-aktif="${Number(obj.active_status)}">
									 <i class="fas fa-pencil-alt"></i>
								 </a>
								 <a href="#" class="btn btn-icon btn-sm btn-danger btn-hapus-kulkul" title="Hapus E-Kulkul"
									 data-toggle="modal" data-target="#modal-hapus-kulkul"
									 data-id="${obj.id}">
									 <i class="fas fa-trash"></i>
								 </a>
							 </div>
							</td>
					 </tr>
				`;
      $("#tabel-kulkul tbody").append(row);
    });

    const maxCode = data.reduce((res, cur) => Number(cur.code) > Number(res) ? Number(cur.code) : Number(res), 0);
    $("#tambah-kode").val(maxCode + 1);

    $(".btn-edit-kulkul").click(e => {
      const id = $(e.currentTarget).attr("data-id");
      const id_desa = $(e.currentTarget).attr("data-id-desa");
      const kode = $(e.currentTarget).attr("data-kode");
      const alamat = $(e.currentTarget).attr("data-alamat");
      const status_aktif = $(e.currentTarget).attr("data-status-aktif");

      $("#edit-id").val(id)
      $("#edit-id-desa").val(id_desa)
      $("#edit-kode").val(kode)
      $("#edit-alamat").val(alamat)
      $("#edit-status-aktif").val(status_aktif)
    })

    $(".btn-hapus-kulkul").click(e => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id)
    })

  }
};
