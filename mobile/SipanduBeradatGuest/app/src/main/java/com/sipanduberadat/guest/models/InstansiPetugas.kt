package com.sipanduberadat.guest.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class InstansiPetugas(var id: Long, var kecamatan: Kecamatan, var jenis_instansi: JenisInstansi,
                           var report_status: Int, var active_status: Boolean): Parcelable