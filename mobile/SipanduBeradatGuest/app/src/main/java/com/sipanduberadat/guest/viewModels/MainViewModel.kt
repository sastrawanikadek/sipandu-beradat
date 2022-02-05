package com.sipanduberadat.guest.viewModels

import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.sipanduberadat.guest.models.*

class MainViewModel: ViewModel() {
    val me = MutableLiveData<Tamu>()
    val reportTypes = MutableLiveData<List<JenisPelaporan>>()
    val reportHistories = MutableLiveData<List<PelaporanTamu>>()
    val reports = MutableLiveData<List<Pelaporan>>()
    val guestReports = MutableLiveData<List<PelaporanTamu>>()
    val news = MutableLiveData<List<Berita>>()
    val accommodationNews = MutableLiveData<List<BeritaAkomodasi>>()
    val blockedRoads = MutableLiveData<List<PenutupanJalan>>()
    val families = MutableLiveData<List<KerabatTamu>>()
    val requestFamilies = MutableLiveData<List<KerabatTamu>>()
}