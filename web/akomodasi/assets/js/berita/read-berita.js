$(document).ready(async () => {
  await readBerita();
  $("#search-berita").keyup((e) => {
    readBerita();
  });
});

const bulan = [
  "Jan",
  "Feb",
  "Mar",
  "Apr",
  "Mei",
  "Jun",
  "Jul",
  "Agu",
  "Sep",
  "Okt",
  "Nov",
  "Des",
];
const hari = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];

const readBerita = async () => {
  var temp;
  var k = 0;
  const searchBerita = $("#search-berita").val();
  const idAkomodasi = localStorage.getItem("id_akomodasi");
  const req = await fetch(
    `https://sipanduberadat.com/api/berita/akomodasi/?id_akomodasi=${idAkomodasi}`
  );
  const { status_code, data } = await req.json();
  if (searchBerita == "") {
    data1 = data;
  } else {
    data1 = data.filter(function filterss(data) {
      k++;
      temp = data.title.toUpperCase();
      if (temp.search(searchBerita.toUpperCase()) !== -1) {
        return data.id == k;
      }
    });
  }

  if (status_code === 200) {
    $("#berita").html("");
    data1.map((obj, i) => {
      const tanggal = new Date(obj.time);
      const full_date = `${hari[tanggal.getDay()]}, ${tanggal.getDate()} ${
        bulan[tanggal.getMonth()]
      } ${tanggal.getFullYear()}, Pukul: ${tanggal.getHours()}:${tanggal.getMinutes()}`;
      const row = `
      <div class="col-xl-4 col-md-6 stretch-card grid-margin stretch-card">
        <div class="card">
          <div class="cover-detail-berita">
            <img src="${obj.cover}">
          </div>
          <div class="card-img-overlay">
            <div class="d-flex justify-content-between align-items-center">
              ${
                Number(obj.active_status) === 1
                  ? "<div class='badge badge-pill badge-primary-red'>Aktif</div>"
                  : "<div class='badge badge-pill badge-secondary'>Tidak Aktif</div>"
              }
              <span class="dropdown dropleft d-block">
                <span id="dropdownMenuButton1" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                  <span><i class="mdi mdi-dots-horizontal-circle text-light" style="font-size: 1.7rem; cursor: pointer;"></i></span>
                </span>
                <span class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                  <button class="dropdown-item btn-edit" data-toggle="modal" data-target="#modal-edit-berita" 
                  data-id='${obj.id}'
                  data-title='${obj.title}'
                  data-content='${obj.content}'
                  data-cover='${obj.cover}'
                  data-active-status='${obj.active_status}'>Edit Berita
                  </button>
                  <button class="dropdown-item btn-delete" data-toggle="modal" data-target="#modal-hapus-berita" 
                    data-id="${obj.id}">Hapus Berita
                  </button>
                </span>
              </span>
            </div>
          </div>
          <div class="card-body">
            <h5 class="card-title font-weight-bold">${obj.title}</h5>
            <p class="card-text content">${
              obj.content.length > 150
                ? obj.content.substring(0, 150) + " . ."
                : obj.content
            }</p>
            <p class="card-text"><small class="text-muted">${full_date}</small></p>
          </div>
          <a class="text-primary p-4 mb-0 d-block h6 text-right" href='detail-berita.html?title=${
            obj.title
          }&cover=${obj.cover}&time=${full_date}&active-status=${
        obj.active_status
      }&content=${
        obj.content
      }' target="_blank" style="z-index:999;">Selengkapnya <i class="mdi mdi-chevron-right"></i></a>          
        </div>
      </div>`;
      $("#berita").append(row);
    });

    $("#berita").on("click", ".btn-edit", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      const title = $(e.currentTarget).attr("data-title");
      const content = $(e.currentTarget).attr("data-content");
      const cover = $(e.currentTarget).attr("data-cover");
      const active_status = $(e.currentTarget).attr("data-active-status");

      $("#edit-id").val(id);
      $("#edit-title").val(title);
      CKEDITOR.instances["edit-content"].setData(content);
      $("#view-cover").attr("src", cover);
      $("#edit-cover").attr("src", cover);
      $("#edit-active-status").val(active_status).change();
    });

    $("#berita").on("click", ".btn-delete", (e) => {
      const id = $(e.currentTarget).attr("data-id");
      $("#hapus-id").val(id);
    });
  }
};
