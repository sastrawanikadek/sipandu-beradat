let more_data = [];
const get_analytic = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/landing-page/analytic/"
  );
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    $("#count-desa").text(data.total_desa_adat);
    $("#count-pecalang").text(data.total_pecalang);
    $("#count-masyarakat").text(data.total_masyarakat);
    $("#count-wisatawan").text(data.total_tamu);
    $("#count-instansi").text(data.total_instansi);
    $("#count-akomodasi").text(data.total_akomodasi);
  }
};

const get_icon = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/jenis-pelaporan/?active_status=true&emergency_status=true"
  );
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    data.map((obj, i) => {
      const icon_pelaporan = `
      <div class="img-pelaporan-wrapper">
        <img src="${obj.icon}" class="img-pelaporan pb-0" alt="" data-aos="zoom-in" data-aos-delay="100">
        <h6 class="font-bold text-dark w-auto">${obj.name}</h6>
      </div>
		`;
      $("#icon-pelaporan").append(icon_pelaporan);
    });
  }
};

const readPelaporanMasyarakat = async () => {
  const req = await fetch(`https://sipanduberadat.com/api/pelaporan/`);
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanMasyarakat();
  }
};

const readPelaporanTamu = async () => {
  const req = await fetch(`https://sipanduberadat.com/api/pelaporan-tamu/`);
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanTamu();
  }
};

const readPelaporanDaruratMasyarakat = async () => {
  const req = await fetch(`https://sipanduberadat.com/api/pelaporan-darurat/`);
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanDaruratMasyarakat();
  }
};

const readPelaporanDaruratTamu = async () => {
  const req = await fetch(
    `https://sipanduberadat.com/api/pelaporan-darurat-tamu/`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readPelaporanDaruratTamu();
  }
};

const readAllPelaporan = async () => {
  const pelaporansMasyarakat = (await readPelaporanMasyarakat()).map((obj) => ({
    ...obj,
    report_type: "pelaporan-masyarakat",
  }));
  const pelaporanDaruratMasyarakat = (
    await readPelaporanDaruratMasyarakat()
  ).map((obj) => ({ ...obj, report_type: "pelaporan-darurat-masyarakat" }));
  const pelaporansTamu = (await readPelaporanTamu()).map((obj) => ({
    ...obj,
    report_type: "pelaporan-tamu",
  }));
  const pelaporanDaruratsTamu = (await readPelaporanDaruratTamu()).map(
    (obj) => ({ ...obj, report_type: "pelaporan-darurat-tamu" })
  );
  const data = [
    ...pelaporansMasyarakat,
    ...pelaporanDaruratMasyarakat,
    ...pelaporanDaruratsTamu,
    ...pelaporansTamu,
  ];

  $("#section-berita").html("");
  data.sort(function (a, b) {
    return Date.parse(b.time) > Date.parse(a.time) ? 1 : -1;
  });
  let index = 0;
  data.map((obj, i) => {
    if (index < 4) {
      img =
        obj.pecalang_reports.length > 0
          ? obj.pecalang_reports[0].photo
          : obj.petugas_reports.length
          ? obj.petugas_reports[0].photo
          : false;
      title = obj.title == undefined ? obj.jenis_pelaporan.name : obj.title;
      description =
        obj.title == undefined ? "Pelaporan Bertipe Darurat" : obj.description;
      if (img) {
        const berita = `
					<div class="col-md-6 d-flex align-items-stretch mt-4">
					<div class="card" style='background-image: url(${img});' data-aos="fade-up"
					data-aos-delay="100">
					<div class="card-body">
						<h5 class="card-title">"${title}"</h5>
						<p class="card-text"> "${description}"</p>
						<div class="read-more"><a href="detail-berita.html?id=${obj.id}&report_type=${obj.report_type}"><i class="icofont-arrow-right"></i> Selengkapnya</a></div>
					</div>
					</div>
				</div>
				`;
        $("#section-berita").append(berita);
        index += 1;
      }
    } else if (index >= 4 && index < 8) {
      img =
        obj.pecalang_reports.length > 0
          ? obj.pecalang_reports[0].photo
          : obj.petugas_reports.length
          ? obj.petugas_reports[0].photo
          : false;
      if (img) {
        more_data.push(obj);
        index += 1;
      }
    }
  });
};

$(".btn-learn-more").click((e) => {
  more_data.map((obj, i) => {
    img =
      obj.pecalang_reports.length > 0
        ? obj.pecalang_reports[0].photo
        : obj.petugas_reports.length
        ? obj.petugas_reports[0].photo
        : false;
    title = obj.title == undefined ? obj.jenis_pelaporan.name : obj.title;
    description =
      obj.title == undefined ? "Pelaporan Bertipe Darurat" : obj.description;

    const berita = `
			<div class="col-md-6 d-flex align-items-stretch mt-4">
			<div class="card" style='background-image: url(${img});' data-aos="fade-up"
			data-aos-delay="100">
			<div class="card-body">
				<h5 class="card-title">${title}"</h5>
				<p class="card-text"> "${description}"</p>
				<div class="read-more"><a href="detail-berita.html?id=${obj.id}&report_type=${obj.report_type}"><i class="icofont-arrow-right"></i> Selengkapnya</a></div>
			</div>
			</div>
		</div>
		`;
    $("#section-berita").append(berita);
    $(".btn-learn-more").hide();
  });
});

$(document).ready(() => {
  get_analytic();
  readAllPelaporan();
  get_icon();
});
