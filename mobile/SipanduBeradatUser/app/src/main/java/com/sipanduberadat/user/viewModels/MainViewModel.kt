package com.sipanduberadat.user.viewModels

import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.sipanduberadat.user.models.*

class MainViewModel: ViewModel() {
    val me = MutableLiveData<Me>()
    val reportTypes = MutableLiveData<List<JenisPelaporan>>()
    val reportHistories = MutableLiveData<List<Pelaporan>>()
    val reports = MutableLiveData<List<Pelaporan>>()
    val guestReports = MutableLiveData<List<PelaporanTamu>>()
    val news = MutableLiveData<List<Berita>>()
    val blockedRoads = MutableLiveData<List<PenutupanJalan>>()
    val families = MutableLiveData<List<Kerabat>>()
    val requestFamilies = MutableLiveData<List<Kerabat>>()
}