package com.sipanduberadat.petugas.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class Negara(var id: Long, var name: String, var flag: String, var active_status: Boolean): Parcelable