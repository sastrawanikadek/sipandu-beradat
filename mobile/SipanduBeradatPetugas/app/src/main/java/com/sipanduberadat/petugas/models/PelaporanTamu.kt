package com.sipanduberadat.petugas.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize
import java.util.*

@Parcelize
data class PelaporanTamu(var id: Long, var tamu: Tamu, var desa_adat: DesaAdat,
                         var jenis_pelaporan: JenisPelaporan, var pecalang_reports: List<PecalangPelaporan>,
                         var petugas_reports: List<PetugasPelaporan>, var time: Date,
                         var latitude: Double, var longitude: Double, var status: Int,
                         var title: String?, var photo: String?, var description: String?): Parcelable