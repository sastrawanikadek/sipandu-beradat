const latitude = Number(getParameterByName("latitude"));
const longitude = Number(getParameterByName("longitude"));
const namaJenisPelaporan = getParameterByName("jenis-pelaporan");

var map;
var marker;
var pos = {
	lat: latitude,
	lng: longitude,
};

function initMyMap() {
	var infoWindow = new google.maps.InfoWindow();
	map = new google.maps.Map(document.getElementById("maps"), {
		center: pos,
		zoom: 12,
	});
	marker = new google.maps.Marker({
		map: map,
		position: pos,
		draggable: true,
		animation: google.maps.Animation.DROP,
	});
	marker.addListener("click", function (e) {
		infoWindow.setPosition(pos);
		infoWindow.setContent(`
			<label>Pelaporan</label>
			<h5 class="text-dark font-weight-bold">${namaJenisPelaporan}</h5>`);
		infoWindow.open(map);
	});
}