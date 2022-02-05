$("#form-mapsbaru").submit(async (e) => {
  e.preventDefault();
  await getDesaAdat1();
});

const getDesaAdat = async () => {
  const req = await fetch(
    "https://sipanduberadat.com/api/desa-adat/?active_status=true"
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    await getDesaAdat();
  }
};
$(document).ready(() => {
  $("#tambah-kabupaten").change((e) => {
    getDesaAdat1();
  });
  $("#tambah-kecamatan").change((e) => {
    getDesaAdat1();
  });
});

const getDesaAdat1 = async () => {
  const desaAdats = await getDesaAdat();
  const namaKabupaten = $("#tambah-kabupaten option:selected").text();
  const namaKecamatan = $("#tambah-kecamatan option:selected").text();
  const infoWindow = new google.maps.InfoWindow();
  map = new google.maps.Map(document.getElementById("maps"), {
    center: {
      lat: -8.3405,
      lng: 115.092,
    },
    zoom: 8,
  });
  desaAdats.map((obj) => {
    if (namaKecamatan === "Pilih Kecamatan") {
      if (obj.kecamatan.kabupaten.name === namaKabupaten) {
        const marker = new google.maps.Marker({
          map: map,
          position: {
            lat: Number(obj.latitude),
            lng: Number(obj.longitude),
          },
        });
        marker.addListener("click", function (e) {
          infoWindow.setPosition({
            lat: Number(obj.latitude),
            lng: Number(obj.longitude),
          });
          infoWindow.setContent(`<h5 style="color: black;">${obj.name}</h5>`);
          infoWindow.open(map, marker);
        });
      }
    } else {
      if (obj.kecamatan.name === namaKecamatan) {
        const marker = new google.maps.Marker({
          map: map,
          position: {
            lat: Number(obj.latitude),
            lng: Number(obj.longitude),
          },
        });
        marker.addListener("click", function (e) {
          infoWindow.setPosition({
            lat: Number(obj.latitude),
            lng: Number(obj.longitude),
          });
          infoWindow.setContent(`<h5 style="color: black;">${obj.name}</h5>`);
          infoWindow.open(map, marker);
        });
      }
    }
  });
};
