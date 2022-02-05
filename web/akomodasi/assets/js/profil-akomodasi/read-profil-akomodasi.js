$(document).ready(async () => {
  const provinsis = await readProvinsi();
  const kabupatens = await readKabupaten();
  const kecamatans = await readKecamatan();
  const desaAdats = await readDesaAdat();
  const fasilitas = await readFasilities();
  readAkomodasi();

  provinsis.map((obj) => {
    const option = `<option value="${obj.id}">${obj.name}</option>`;
    $("#edit-provinsi").append(option);
  });

  fasilitas.map((obj) => {
    const option = ` <div class="col-md-4 mb-4">
    <input class="form-control-label" type="checkbox" name="fasilitas" value="${obj.id}">
    <label for="flexCheckDefault">${obj.name}</label>
  </div>`;
    $("#edit-fasilitas").append(option);
  });
  $("#edit-provinsi").change((e) => {
    if (e.target.value) {
      $("#edit-kabupaten").removeAttr("disabled");
      $("#edit-kabupaten").html("");
      ("");
      kabupatens
        .filter((obj) => obj.provinsi.id === Number(e.target.value))
        .map((obj) => {
          const option = `<option value="${obj.id}">${obj.name}</option>`;
          $("#edit-kabupaten").append(option);
        });
    } else {
      $("#edit-kabupaten").attr("disabled", "disabled");
    }
  });
  $("#edit-kabupaten").change((e) => {
    if (e.target.value) {
      $("#edit-kecamatan").removeAttr("disabled");
      $("#edit-kecamatan").html("");
      ("");
      kecamatans
        .filter((obj) => obj.kabupaten.id === Number(e.target.value))
        .map((obj) => {
          const option = `<option value="${obj.id}">${obj.name}</option>`;
          $("#edit-kecamatan").append(option);
        });
    } else {
      $("#edit-kecamatan").attr("disabled", "disabled");
    }
  });
  $("#edit-kecamatan").change((e) => {
    if (e.target.value) {
      $("#edit-desa-adat").removeAttr("disabled");
      $("#edit-desa-adat").html("");
      ("");
      desaAdats
        .filter((obj) => obj.kecamatan.id === Number(e.target.value))
        .map((obj) => {
          const option = `<option value="${obj.id}">${obj.name}</option>`;
          $("#edit-desa-adat").append(option);
        });
    } else {
      $("#edit-desa-adat").attr("disabled", "disabled");
    }
  });
});

const readProvinsi = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/provinsi/?active_status=true"
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readProvinsi();
  }
};
const readKabupaten = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/kabupaten/?active_status=true"
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readKabupaten();
  }
};

const readKecamatan = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/kecamatan/?active_status=true"
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readKecamatan();
  }
};

const readDesaAdat = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/desa-adat/?active_status=true"
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readDesaAdat();
  }
};

const readFasilities = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/fasilitas/?active_status=true"
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    readFasilities();
  }
};

$("#edit-logo").change((e) => {
  if (e.currentTarget.files.length > 0) {
    $("#label-edit-logo").text(e.currentTarget.files[0].name);
  } else {
    $("#label-edit-logo").text("Select file");
  }
});

$("#edit-cover").change((e) => {
  if (e.currentTarget.files.length > 0) {
    $("#label-edit-cover").text(e.currentTarget.files[0].name);
  } else {
    $("#label-edit-cover").text("Select file");
  }
});

const readAkomodasi = async () => {
  const id_akomodasi = localStorage.getItem("id_akomodasi");
  const req = await fetch(
    `https://sipanduberadat.com/api/fasilitas-akomodasi/?id_akomodasi=${id_akomodasi}`
  );

  const { status_code, message, data } = await req.json();

  if (status_code === 200) {
    $("#akomodasi-fasilitas").html("");
    $("#akomodasi-name").html(data.akomodasi.name);
    $("#akomodasi-desc").html(data.akomodasi.description);
    $("#akomodasi-logo").attr("src", data.akomodasi.logo);
    $("#akomodasi-cover").attr("src", data.akomodasi.cover);
    $("#akomodasi-lokasi").html(data.akomodasi.location);
    $("#akomodasi-lokasi-1").html(
      `${data.akomodasi.desa_adat.kecamatan.kabupaten.provinsi.name}, ${data.akomodasi.desa_adat.kecamatan.kabupaten.name}`
    );
    $("#akomodasi-lokasi-2").html(
      `${data.akomodasi.desa_adat.kecamatan.name}, ${data.akomodasi.desa_adat.name}`
    );

    for (index = 0; index < data.facilities.length; index++) {
      $("#akomodasi-fasilitas").prepend(`
          <div class="col-md-2 fasilitas d-flex flex-column align-items-center">
            <div class="icon icon-shape bg-gradient-red text-white rounded-circle shadow">
              <img src=${data.facilities[index].icon} style="height: 35px; width: 35px;">
            </div>
            <span class="description text-center mb-2">${data.facilities[index].name}</span>
          </div>
          `);
      $("input[name='fasilitas']").map((i, obj) =>
        data.facilities[index].id === Number($(obj).val())
          ? $(obj).prop("checked", true)
          : ""
      );
    }

    $("#btn-update-profile").attr({
      "data-fasilitas": data.akomodasi.facilities,
      "data-id": data.akomodasi.id,
      "data-name": data.akomodasi.name,
      "data-logo": data.akomodasi.logo,
      "data-desc": data.akomodasi.description,
      "data-cover": data.akomodasi.cover,
      "data-lokasi": data.akomodasi.location,
      "data-id-desa": data.akomodasi.desa_adat.id,
      "data-desa": data.akomodasi.desa_adat.name,
      "data-id-kecamatan": data.akomodasi.desa_adat.kecamatan.id,
      "data-kecamatan": data.akomodasi.desa_adat.kecamatan.name,
      "data-id-kabupaten": data.akomodasi.desa_adat.kecamatan.kabupaten.id,
      "data-kabupaten": data.akomodasi.desa_adat.kecamatan.kabupaten.name,
      "data-id-provinsi":
        data.akomodasi.desa_adat.kecamatan.kabupaten.provinsi.id,
      "data-provinsi":
        data.akomodasi.desa_adat.kecamatan.kabupaten.provinsi.name,
    });

    $("#btn-update-profile").click((e) => {
      const fasilitas = $(e.currentTarget).attr("data-fasilitas");
      const id = $(e.currentTarget).attr("data-id");
      const name = $(e.currentTarget).attr("data-name");
      const logo = $(e.currentTarget).attr("data-logo");
      const desc = $(e.currentTarget).attr("data-desc");
      const cover = $(e.currentTarget).attr("data-cover");
      const lokasi = $(e.currentTarget).attr("data-lokasi");
      const id_desa = $(e.currentTarget).attr("data-id-desa");
      const desa = $(e.currentTarget).attr("data-desa");
      const id_kecamatan = $(e.currentTarget).attr("data-id-kecamatan");
      const kecamatan = $(e.currentTarget).attr("data-kecamatan");
      const id_kabupaten = $(e.currentTarget).attr("data-id-kabupaten");
      const kabupaten = $(e.currentTarget).attr("data-kabupaten");
      const id_provinsi = $(e.currentTarget).attr("data-id-provinsi");
      const provinsi = $(e.currentTarget).attr("data-provinsi");

      $("#edit-facilites").val(fasilitas);
      $("#edit-id").val(id);
      $("#edit-name").val(name);
      $("#edit-desc").val(desc);
      $("#edit-address").val(lokasi);
      $("#edit-provinsi")
        .find(`option[value=${id_provinsi}]`)
        .attr("selected", "selected")
        .change();
      $("#edit-kabupaten")
        .find(`option[value=${id_kabupaten}]`)
        .attr("selected", "selected")
        .change();
      $("#edit-kecamatan")
        .find(`option[value=${id_kecamatan}]`)
        .attr("selected", "selected")
        .change();
      $("#edit-desa-adat")
        .find(`option[value=${id_desa}]`)
        .attr("selected", "selected")
        .change();
    });
  } else if (status_code === 401) {
    refreshToken(readAkomodasi);
  }
};
const getMe = async () => {
  const fd = new FormData();
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/admin-akomodasi/me/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    localStorage.setItem("id_akomodasi", data.pegawai.akomodasi.id);
    localStorage.setItem("name", data.pegawai.name);
    localStorage.setItem("name_akomodasi", data.pegawai.akomodasi.name);
    localStorage.setItem("avatar", data.pegawai.avatar);
  } else {
    getMe();
  }
};

$("tbody").on("click", ".modal-img", function () {
  $("#modal-show-img").modal("show");
  const img_url = $(this).attr("src");
  $(".show-img").attr("src", img_url);
});
