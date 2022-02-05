package com.sipanduberadat.user.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class Notifikasi(var id: Long, var photo: String, var title: String, var description: String,
                      var type: Int, var data: Long): Parcelable