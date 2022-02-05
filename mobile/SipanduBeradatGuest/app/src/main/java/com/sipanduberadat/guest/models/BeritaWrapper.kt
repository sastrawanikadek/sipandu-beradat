package com.sipanduberadat.guest.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class BeritaWrapper(var news: Berita?, var accommodationNews: BeritaAkomodasi?, var blockedRoad: PenutupanJalan?,
                         var report: Pelaporan?, var guestReport: PelaporanTamu?): Parcelable