$(document).ready(() => {
  read_banjar()
  read_krama();
});

const read_banjar = async () => {
  const idDesa = localStorage.getItem("id_desa")
  const req = await fetch(`https://api-sipandu-beradat.000webhostapp.com/banjar/?id_desa=${idDesa}&active_status=true`);
  const {
    status_code,
    data
  } = await req.json();

  if (status_code === 200) {
    data.map(obj => {
      const option = `<option value="${obj.id}">${obj.name}</option>`;
      $("#tambah-banjar").append(option)
    })
  } else {
    read_banjar()
  }
}

const read_krama = async () => {
  const idDesa = localStorage.getItem("id_desa")
  const req = await fetch(`https://api-sipandu-beradat.000webhostapp.com/masyarakat/?id_desa=${idDesa}`);
  const {
    status_code,
    data,
    message
  } = await req.json();

  if (status_code === 200) {
    $("#tabel-krama-desa tbody").html("")
    data.map((obj, i) => {
      const row = `
				  <tr>
						<td>${i + 1}</td>
						<td>${obj.name}</td>
						<td>${obj.category}</td>
						<td>${obj.phone}</td>
						<td>${obj.date_of_birth}</td>
						<td>${obj.nik}</td>
						<td>${obj.gender}</td>
						<td>${obj.banjar.name}</td>
						${obj.valid_status ? '<td><div class="badge badge-success">Valid</div></td>' : '<td><div class="badge badge-secondary">Belum Valid</div></td>'}
						${obj.active_status ? '<td><div class="badge badge-success">Aktif</div></td>' : '<td><div class="badge badge-secondary">Tidak Aktif</div></td>'}
						${obj.block_status ? '<td><div class="badge badge-danger">Diblokir</div></td>' : '<td><div class="badge badge-primary">Tidak Diblokir</div></td>'}
		
						<td>
						  <div class="d-flex">
							</a>
								<a href="#" class="btn btn-icon btn-sm btn-success btn-validasi-krama-desa mr-2" title="Validasi Krama Desa Adat"
									data-toggle="modal" data-target="#modal-validasi-krama-desa"
									data-id="${obj.id}">
								<i class="fas fa-check"></i>
							</a>
							 <a href="profil-krama-desa-adat.html?view-name=${obj.name
                }&view-avatar=${obj.avatar
                }&view-valid-status=${obj.valid_status
                }&view-active-status=${obj.active_status
                }&view-block-status=${obj.block_status
                }&jenis-krama=${obj.category
                }&gender=${obj.gender
                }&birth=${obj.date_of_birth
                }&nik=${obj.nik
                }&phone=${obj.phone
                }&banjar=${obj.banjar.name
                }&desa-adat=${obj.banjar.desa_adat.name
                }&kecamatan=${obj.banjar.desa_adat.kecamatan.name
                }&kabupaten=${obj.banjar.desa_adat.kecamatan.kabupaten.name
                }"title="Profil Krama Desa Adat"
								class="btn btn-sm btn-icon btn-info mr-2">
								<i class="fas fa-user"></i></a>
							 <a href="monitor-krama-desa-adat.html" title="Lokasi Krama Desa Adat"
								class="btn btn-sm btn-icon btn-warning mr-2 btn-validasi-krama-desa"><i class="fas fa-map"></i></a>
							 <a href="#" title="Edit Krama Desa Adat" class="btn btn-sm btn-icon btn-primary btn-edit-krama-desa mr-2"
								data-toggle="modal" data-target="#modal-edit-krama-desa" 
								data-id="${obj.id}"
								data-id-banjar="${obj.banjar.id}"
								data-nama="${obj.name}"
								data-telp="${obj.phone}"
								data-tanggal-lahir="${obj.date_of_birth}"
								data-nik="${obj.nik}"
								data-jenis-kelamin="${obj.gender === "Laki-laki" ? "l" : "p"}"
								data-avatar="${obj.avatar}"
								data-jenis-krama="${obj.category === "Krama Wid" ? 0 : obj.category === "Krama Tamiu" ? 1 : 2}"
								data-status-valid="${Number(obj.valid_status)}"
								data-status-aktif="${Number(obj.active_status)}">
								<i class="fas fa-pencil-alt"></i>
							 </a>
								<a href="#" class="btn btn-icon btn-sm btn-danger btn-hapus-krama-desa" title="Hapus Krama Desa Adat"
								data-toggle="modal" data-target="#modal-hapus-krama-desa"
								data-id="${obj.id}">
								<i class="fas fa-trash"></i>
							</a>
						  </div>
						</td>
				  </tr>
			 `;
      $("#tabel-krama-desa tbody").append(row);

    });

    $(".btn-edit-krama-desa").click(e => {
      const id = $(e.currentTarget).attr("data-id");
      const id_banjar = $(e.currentTarget).attr("data-id-banjar");
      const name = $(e.currentTarget).attr("data-nama");
      const avatar = $(e.currentTarget).attr("data-avatar");
      const phone = $(e.currentTarget).attr("data-telp");
      const nik = $(e.currentTarget).attr("data-nik");
      const jenis_kelamin = $(e.currentTarget).attr("data-jenis-kelamin");
      const valid_status = $(e.currentTarget).attr("data-status-valid");
      const active_status = $(e.currentTarget).attr("data-status-aktif");
      const date_of_birth = $(e.currentTarget).attr("data-tanggal-lahir");
      const category = $(e.currentTarget).attr("data-jenis-krama");

      $("#edit-id").val(id)
      $("#edit-id-banjar").val(id_banjar)
      $("#edit-nama").val(name)
      $("#edit-avatar-img").attr("src", avatar);
      $("#edit-telp").val(phone)
      $("#edit-nik").val(nik)
      $("#edit-jenis-kelamin").val(jenis_kelamin)
      $("#edit-status-valid").val(valid_status)
      $("#edit-status-aktif").val(active_status)
      $("#edit-tanggal-lahir").val(date_of_birth)
      $("#edit-jenis-krama").val(category)
    })

    $(".btn-hapus-krama-desa").click(e => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id)
    })

    $(".btn-validasi-krama-desa").click(e => {
      const id = $(e.currentTarget).attr("data-id");
      $("#validasi-id").val(id)
    })


  }
};
