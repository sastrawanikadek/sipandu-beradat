$(document).ready(() => {
  readDetailBerita();
});

const readDetailBerita = async () => {
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
  startLoading();
  const id = getParameterByName("id_berita");
  const req = await fetch(`https://sipanduberadat.com/api/berita/desa-adat/`);
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    stopLoading();
    data.map((obj, i) => {
      if (obj.id === Number(id)) {
        const tanggal = new Date(obj.time);
        const full_date = `${hari[tanggal.getDay()]}, ${tanggal.getDate()} ${
          bulan[tanggal.getMonth()]
        } ${tanggal.getFullYear()}, Pukul: ${tanggal.getHours()}:${tanggal.getMinutes()}`;
        $("#title").html(obj.title);
        $("#content").html(obj.content);
        $("#time").html(full_date);
        $("#cover").attr("src", obj.cover);
        $("#active-status").html(
          obj.active_status
            ? '<div class="badge badge-pill badge-primary-red mr-2">Aktif</div>'
            : '<div class="badge badge-pill badge-secondary mr-2">Tidak Aktif</div>'
        );
      }
    });
  }
};
