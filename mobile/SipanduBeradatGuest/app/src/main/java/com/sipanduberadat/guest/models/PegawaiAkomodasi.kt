package com.sipanduberadat.guest.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize
import java.util.*

@Parcelize
data class PegawaiAkomodasi(var id: Long, var akomodasi: Akomodasi, var name: String,
                            var avatar: String, var phone: String, var date_of_birth: Date,
                            var nik: String, var gender: String, var active_status: Boolean): Parcelable