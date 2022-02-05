package com.sipanduberadat.guest.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class StatusAktif(var id: Long, var name: String, var status: Boolean,
                       var active_status: Boolean): Parcelable