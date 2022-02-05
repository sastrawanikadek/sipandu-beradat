package com.sipanduberadat.guest.viewModels

import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.sipanduberadat.guest.models.Pelaporan
import com.sipanduberadat.guest.models.PelaporanTamu

class ReportDetailViewModel: ViewModel() {
    val report: MutableLiveData<Pelaporan> = MutableLiveData()
    val guestReport: MutableLiveData<PelaporanTamu> = MutableLiveData()
}