package com.sipanduberadat.user.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class Sirine(var id: Long, var desa_adat: DesaAdat, var code: String, var photo: String,
                  var location: String, var active_status: Boolean): Parcelable