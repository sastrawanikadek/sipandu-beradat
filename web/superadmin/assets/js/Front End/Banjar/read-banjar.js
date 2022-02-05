$(document).ready(() => {
  read_banjar();
});

const read_banjar = async () => {
  // const idDesa = localStorage.getItem("id_desa")
  const req = await fetch(
    `https://api-sipandu-beradat.000webhostapp.com/banjar/$*i`
  );
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    $("#tabel-banjar tbody").html("");
    data.map((obj, i) => {
      const row = `
				 <tr>
					 <td>${i + 1}</td>
					 <td>${obj.name}</td>
					 ${
             obj.active_status
               ? '<td><label class="badge badge-success">Aktif</label>'
               : '<td><label class="badge badge-secondary">Tidak Aktif</label></td>'
           }
           <td>
            <div class="d-flex align-items-center">
              <button type="button" class="btn btn-inverse-primary btn-rounded btn-icon btn-action btn-edit-banjar mr-2"
                title="Edit Banjar" data-toggle="modal" data-target="#modal-edit-banjar"
                data-id="${obj.id}"
								data-id-desa="${obj.desa_adat.id}"
								data-nama="${obj.name}" 
								data-status-aktif="${Number(obj.active_status)}">
                <i class="mdi mdi-pencil"></i>
              </button>
              <button type="button" class="btn btn-inverse-primary-red btn-rounded btn-icon btn-action btn-hapus-banjar mr-2"
                title="Hapus Banjar" data-toggle="modal" data-target="#modal-hapus-banjar" 
                data-id="${obj.id}">
                <i class="mdi mdi-delete"></i>
              </button>
            </div>
          </td>
				 </tr>
		 `;
      $("#tabel-banjar tbody").append(row);
    });

    $(".btn-edit-banjar").click((e) => {
      const id = $(e.currentTarget).attr("data-id");
      const id_desa = $(e.currentTarget).attr("data-id-desa");
      const name = $(e.currentTarget).attr("data-nama");
      const active_status = $(e.currentTarget).attr("data-status-aktif");

      $("#edit-id").val(id);
      $("#edit-id-desa").val(id_desa);
      $("#edit-name").val(name);
      $("#edit-active-status").val(active_status);
    });

    $(".btn-hapus-banjar").click((e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id);
    });
  }
};
