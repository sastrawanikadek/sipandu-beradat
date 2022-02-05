$(document).ready(async () => {
	if (!localStorage.getItem("avatar")) {
		window.location.href = "https://sipanduberadat.com/instansi/pages/login/"
		return
	}

	$(".nama-admin").html(localStorage.getItem("name"))
	$(".avatar-admin").attr("src", localStorage.getItem("avatar"))
	$(".alamat-instansi").text(localStorage.getItem("alamat_instansi"))
	$(".nama-instansi").text(localStorage.getItem("jenis_instansi"))

	if (localStorage.getItem('super_admin_status') === 'false'){
		$('#sidenav-admin').hide()
	}
});