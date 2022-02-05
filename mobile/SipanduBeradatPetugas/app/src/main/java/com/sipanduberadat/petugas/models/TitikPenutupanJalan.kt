package com.sipanduberadat.petugas.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class TitikPenutupanJalan(var id: Long, var coordinates: List<KoordinatTitikPenutupanJalan>,
                               var type: Int): Parcelable