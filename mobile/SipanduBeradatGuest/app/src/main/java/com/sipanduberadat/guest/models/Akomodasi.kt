package com.sipanduberadat.guest.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class Akomodasi(var id: Long, var desa_adat: DesaAdat, var cover: String, var description: String,
                     var logo: String, var name: String, var location: String,
                     var active_status: Boolean): Parcelable {
    override fun toString(): String {
        return name
    }
 }