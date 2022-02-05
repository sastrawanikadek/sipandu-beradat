package com.sipanduberadat.petugas.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize
import java.util.*

@Parcelize
data class Tamu(var id: Long, var akomodasi: Akomodasi, var negara: Negara, var name: String,
                var avatar: String, var phone: String, var date_of_birth: Date,
                var identity_type: String, var identity_number: String, var gender: String,
                var block_status: Boolean, var active_status: Boolean, var username: String?): Parcelable