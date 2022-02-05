package com.sipanduberadat.guest.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class JenisInstansi(var id: Long, var name: String, var active_status: Boolean): Parcelable