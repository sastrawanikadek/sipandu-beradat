package com.sipanduberadat.user.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class Pecalang(var id: Long, var masyarakat: Krama, var sirine_authority: Boolean,
                    var prajuru_status: Boolean, var working_status: Boolean,
                    var active_status: Boolean): Parcelable