package com.sipanduberadat.petugas.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class JenisPelaporan(var id: Long, var name: String, var icon: String,
                          var emergency_status: Boolean, var active_status: Boolean): Parcelable {
    override fun toString(): String {
        return name
    }
}