package com.sipanduberadat.petugas.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class ReportWrapper(var report: Pelaporan?, var guestReport: PelaporanTamu?): Parcelable