package com.sipanduberadat.guest.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize
import java.util.*

@Parcelize
data class Berita(var id: Long, var admin_desa: AdminDesa, var title: String, var cover: String,
                  var content: String, var time: Date, var active_status: Boolean): Parcelable