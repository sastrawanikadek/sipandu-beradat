$(document).ready(() => {
  readBlokir();
});

const readBlokir = async () => {
  const idDesa = localStorage.getItem("id_desa")
  const req = await fetch(`https://api-sipandu-beradat.000webhostapp.com/masyarakat/?block_status=true&id_desa=${idDesa}`);
  const {
    status_code,
    data,
    message
  } = await req.json();

  if (status_code === 200) {
    $("#tabel-banjar tbody").html("")
    data.map((obj, i) => {
      const row = `
				 <tr>
					 <td>${i + 1}</td>
					 <td>${obj.masyrakat.name}</td>
           <td>${obj.masyrakat.phone}</td>
           <td>${obj.masyrakat.category}</td>
           <td>${obj.banjar.name}</td>
					 <td>
						 <div class="container-crud d-flex">
							<a href="#" class="btn btn-icon btn-sm btn-danger btn-buka-blokir" title="Buka Blokir"
								data-toggle="modal" data-target="#modal-buka-blokir" 
								data-id="${obj.id}">
								<i class="fas fa-trash"></i>
							</a>
						 </div>
					 </td>
				 </tr>
		 `;
      $("#tabel-banjar tbody").append(row);
    });

    $(".btn-buka-blokir").click(e => {
      const id = $(e.currentTarget).attr("data-id");

      $("#buka-blokir-id").val(id)
    })

  }
};
