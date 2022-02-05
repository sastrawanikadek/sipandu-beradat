package com.sipanduberadat.user.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class AdminDesa(var id: Long, var masyarakat: Krama, var active_status: Boolean,
                     var super_admin_status: Boolean): Parcelable