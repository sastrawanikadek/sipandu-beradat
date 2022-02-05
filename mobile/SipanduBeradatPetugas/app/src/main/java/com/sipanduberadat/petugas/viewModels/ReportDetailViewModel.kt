package com.sipanduberadat.petugas.viewModels

import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.sipanduberadat.petugas.models.Pelaporan
import com.sipanduberadat.petugas.models.PelaporanTamu

class ReportDetailViewModel: ViewModel() {
    val report: MutableLiveData<Pelaporan> = MutableLiveData()
    val guestReport: MutableLiveData<PelaporanTamu> = MutableLiveData()
}