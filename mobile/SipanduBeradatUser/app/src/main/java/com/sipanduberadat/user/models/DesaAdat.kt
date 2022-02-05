package com.sipanduberadat.user.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class DesaAdat(var id: Long, var kecamatan: Kecamatan, var name: String, var latitude: Double,
                    var longitude: Double, var active_status: Boolean): Parcelable {
    override fun toString(): String {
        return name
    }
}