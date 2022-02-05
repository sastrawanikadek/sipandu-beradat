package com.sipanduberadat.petugas.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class Banjar(var id: Long, var desa_adat: DesaAdat, var name: String,
                  var active_status: Boolean): Parcelable {
    override fun toString(): String {
        return name
    }
}