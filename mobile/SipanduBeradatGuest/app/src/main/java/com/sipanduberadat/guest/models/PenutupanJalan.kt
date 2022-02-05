package com.sipanduberadat.guest.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize
import java.util.*

@Parcelize
data class PenutupanJalan(var id: Long, var pecalang: Pecalang, var points: List<TitikPenutupanJalan>,
                          var title: String, var cover: String, var start_time: Date,
                          var end_time: Date): Parcelable