package com.sipanduberadat.petugas.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class Provinsi(var id: Long, var negara: Negara, var name: String,
                    var active_status: Boolean): Parcelable