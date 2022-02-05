package com.sipanduberadat.guest.models

import android.os.Parcelable
import kotlinx.android.parcel.Parcelize

@Parcelize
data class AdminAkomodasi(var id: Long, var pegawai: PegawaiAkomodasi, var active_status: Boolean,
                          var super_admin_status: Boolean): Parcelable