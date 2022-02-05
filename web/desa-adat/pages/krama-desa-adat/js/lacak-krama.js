const readLacakKrama = async () => {
  const id = Number(getParameterByName("id_krama"));
  const req = await fetch(
    `https://sipanduberadat.com/api/masyarakat/find-location-history/?id_masyarakat=${id}`
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    return data;
  } else {
    await readLacakKrama();
  }
};

async function initMap() {
  const data = await readLacakKrama();
  const map = new google.maps.Map(document.getElementById("map"), {
    zoom: 15,
    center: {
      lat: -8.3405,
      lng: 115.092,
    },
  });

  if (data.histories.length < 1) {
    $("#map-canvas").hide();
    $(".text-canvas").show();
  }

  data.histories.map((obj) => {
    const infoWindow = new google.maps.InfoWindow();
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
    const tanggal = new Date(obj.time);
    const full_date = `${tanggal.getDate()} ${
      bulan[tanggal.getMonth()]
    } ${tanggal.getFullYear()}, ${tanggal.getHours()}:${tanggal.getMinutes()}`;

    const marker = new google.maps.Marker({
      map: map,
      animation: google.maps.Animation.DROP,
      position: {
        lat: Number(obj.latitude),
        lng: Number(obj.longitude),
      },
    });

    window.setTimeout(function () {
      infoWindow.setContent(`
			<label>Waktu</label>
			<h6 class="text-dark font-weight-bold">${full_date}</h6>`);
      infoWindow.open(map, marker);
    }, 2000);

    marker.addListener("click", () => {
      infoWindow.setContent(`<label>Waktu</label>
									<h6 class="text-dark font-weight-bold">${full_date}</h6>`);
      infoWindow.open(map, marker);
    });
  });
}
