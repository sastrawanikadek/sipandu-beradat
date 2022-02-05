$(document).ready(() => {
	const title = getParameterByName("title");
	const content = getParameterByName("content");
	const cover = getParameterByName("cover");
	const time = getParameterByName("time");
	const active_status = getParameterByName("active-status");

	$("#title").html(title);
	$("#content").html(content);
	$("#time").html(time);
	$("#cover").attr("src", cover);
	$("#active-status").html(active_status === "true" ? '<div class="badge badge-pill badge-primary-red mr-2">Aktif</div>' : '<div class="badge badge-pill badge-secondary mr-2">Tidak Aktif</div>');
});