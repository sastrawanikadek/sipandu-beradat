package com.sipanduberadat.guest.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class Kecamatan(var id: Long, var kabupaten: Kabupaten, var name: String,
                     var active_status: Boolean): Parcelable {
    override fun toString(): String {
        return name
    }
}