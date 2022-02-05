package com.sipanduberadat.petugas.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class InstansiPetugas(var id: Long, var kecamatan: Kecamatan, var jenis_instansi: JenisInstansi,
                           var name: String, var report_status: Int, var active_status: Boolean): Parcelable