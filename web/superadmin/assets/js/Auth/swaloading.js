
function swaloading(status_code,message,prov,updateProvinsi,refreshToken){
    if (status_code === 200) {
        Swal.fire({
        title: "Berhasil!",
        text: message,
        icon: "success",
        confirmButtonText: '<i class="fas fa-tachometer-alt pr-2"></i>Berhasil',
        }).then((result) => {
        if (result.isConfirmed) {
            window.location.href ="../../../../"+prov;
        }
        });
    } else if (status_code === 400) {
        Swal.fire({
        title: "Terjadi Kesalahan",
        text: message,
        icon: "error",
        confirmButtonText: "Tutup",
        });
    }else if(status_code === 401){
        refreshToken(updateProvinsi);
    }

}

