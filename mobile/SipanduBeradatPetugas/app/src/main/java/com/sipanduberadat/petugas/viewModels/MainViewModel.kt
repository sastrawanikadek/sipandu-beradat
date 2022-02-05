package com.sipanduberadat.petugas.viewModels

import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.sipanduberadat.petugas.models.*

class MainViewModel: ViewModel() {
    val me = MutableLiveData<Petugas>()
    val reportTypes = MutableLiveData<List<JenisPelaporan>>()
    val reports = MutableLiveData<List<Pelaporan>>()
    val guestReports = MutableLiveData<List<PelaporanTamu>>()
}