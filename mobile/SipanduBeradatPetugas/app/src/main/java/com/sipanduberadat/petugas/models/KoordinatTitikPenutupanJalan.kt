package com.sipanduberadat.petugas.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class KoordinatTitikPenutupanJalan(var id: Long, var latitude: Double, var longitude: Double): Parcelable