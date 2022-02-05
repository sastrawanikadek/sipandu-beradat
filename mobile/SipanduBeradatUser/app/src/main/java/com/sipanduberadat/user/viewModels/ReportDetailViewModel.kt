package com.sipanduberadat.user.viewModels

import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.sipanduberadat.user.models.Pelaporan
import com.sipanduberadat.user.models.PelaporanTamu

class ReportDetailViewModel: ViewModel() {
    val report: MutableLiveData<Pelaporan> = MutableLiveData()
    val guestReport: MutableLiveData<PelaporanTamu> = MutableLiveData()
}