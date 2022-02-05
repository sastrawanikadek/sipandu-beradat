package com.sipanduberadat.guest.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class Pecalang(var id: Long, var masyarakat: Krama, var sirine_authority: Boolean, var working_status: Boolean,
                    var active_status: Boolean): Parcelable