package com.sipanduberadat.petugas.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class Kerabat(var id: Long, var masyarakat: Krama, var pelaporan: List<Pelaporan>,
                   var status: Int, var initiator_status: Boolean): Parcelable