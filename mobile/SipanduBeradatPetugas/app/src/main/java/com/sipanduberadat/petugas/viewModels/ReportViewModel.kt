package com.sipanduberadat.petugas.viewModels

import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import java.util.*

class ReportViewModel: ViewModel() {
    val reportCategory: MutableLiveData<Int> = MutableLiveData()
    val reporterCategory: MutableLiveData<Int> = MutableLiveData()
    val reportTypes: MutableLiveData<MutableList<Long>> = MutableLiveData()
    val startDate: MutableLiveData<Calendar> = MutableLiveData()
    val endDate: MutableLiveData<Calendar> = MutableLiveData()
}