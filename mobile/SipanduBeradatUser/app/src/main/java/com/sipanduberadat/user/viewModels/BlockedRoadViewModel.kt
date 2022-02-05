package com.sipanduberadat.user.viewModels

import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.google.android.gms.maps.model.Marker
import java.util.*

class BlockedRoadViewModel: ViewModel() {
    val currentPage = MutableLiveData(0)
    val mode = MutableLiveData(0)
    val title = MutableLiveData("")
    val startTime = MutableLiveData("")
    val endTime = MutableLiveData("")
    val cover = MutableLiveData<ByteArray>()
    val startTimeDate = MutableLiveData<Calendar>()
    val endTimeDate = MutableLiveData<Calendar>()
    val blockedRoads = MutableLiveData<MutableList<MutableList<List<Double>>>>(mutableListOf())
    val allowedRoads = MutableLiveData<MutableList<MutableList<List<Double>>>>(mutableListOf())
}