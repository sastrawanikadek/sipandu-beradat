package com.sipanduberadat.petugas.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize
import java.util.*

@Parcelize
data class Krama(var id: Long, var banjar: Banjar, var active_status: StatusAktif, var name: String,
                 var avatar: String, var phone: String, var date_of_birth: Date, var nik: String,
                 var gender: String, var category: String, var block_status: Boolean, var valid_status: Boolean,
                 var username: String?, var home_latitude: Double?, var home_longitude: Double?): Parcelable