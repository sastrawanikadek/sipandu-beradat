package com.sipanduberadat.petugas.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class PecalangPelaporan(var id: Long, var pecalang: Pecalang, var status: Int,
                             var photo: String?): Parcelable