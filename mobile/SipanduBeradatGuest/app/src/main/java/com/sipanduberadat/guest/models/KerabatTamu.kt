package com.sipanduberadat.guest.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class KerabatTamu(var id: Long, var tamu: Tamu, var pelaporan: List<PelaporanTamu>,
                   var status: Int, var initiator_status: Boolean): Parcelable