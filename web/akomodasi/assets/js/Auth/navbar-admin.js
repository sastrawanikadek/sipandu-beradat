$(document).ready(async () => {
	init()
});

const init = () => {
	if (!localStorage.getItem("avatar")) {
		window.location.href = "https://sipanduberadat.com/akomodasi/pages/login/";
		return
	}

	$("#nama-admin").html(localStorage.getItem("name"))
	$("#nama-akomodasi").html(localStorage.getItem("name_akomodasi"))
	$("#avatar-admin").attr("src", localStorage.getItem("avatar"))
	
	$("#nav-nama-admin").html(localStorage.getItem("name"))
	$("#nav-nama-akomodasi").html(localStorage.getItem("name_akomodasi"))
	$("#nav-avatar-admin").attr("src", localStorage.getItem("avatar"))
	
	if (localStorage.getItem('super_admin_status') === 'false'){
		$('#sidenav-admin').hide()
	}
}