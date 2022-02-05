package com.sipanduberadat.petugas.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class Kabupaten(var id: Long, var provinsi: Provinsi, var name: String,
                     var active_status: Boolean): Parcelable {
    override fun toString(): String {
        return name
    }
}