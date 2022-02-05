package com.sipanduberadat.guest.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class PetugasPelaporan(var id: Long, var petugas: Petugas, var status: Int,
                             var photo: String?): Parcelable