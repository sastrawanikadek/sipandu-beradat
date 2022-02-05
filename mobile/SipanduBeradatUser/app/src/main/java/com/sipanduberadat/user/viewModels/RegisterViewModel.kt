package com.sipanduberadat.user.viewModels

import android.location.Location
import androidx.lifecycle.MutableLiveData
import androidx.lifecycle.ViewModel
import com.sipanduberadat.user.models.Banjar
import com.sipanduberadat.user.models.DesaAdat
import com.sipanduberadat.user.models.Kabupaten
import com.sipanduberadat.user.models.Kecamatan
import java.util.*

class RegisterViewModel: ViewModel() {
    val name = MutableLiveData("")
    val email = MutableLiveData("")
    val username = MutableLiveData("")
    val password = MutableLiveData("")
    val phone = MutableLiveData("")
    val dateOfBirth = MutableLiveData("")
    val nik = MutableLiveData("")
    val gender = MutableLiveData("")
    val category = MutableLiveData("")
    val kabupaten = MutableLiveData<Long>(-1)
    val kecamatan = MutableLiveData<Long>(-1)
    val desaAdat = MutableLiveData<Long>(-1)
    val banjar = MutableLiveData<Long>(-1)
    val avatar = MutableLiveData<ByteArray>()
    val homeLocation = MutableLiveData<Location>()
    val dateOfBirthDate = MutableLiveData(Calendar.getInstance())
    val kabupatens = MutableLiveData<List<Kabupaten>>(listOf())
    val kecamatans = MutableLiveData<List<Kecamatan>>(listOf())
    val desaAdats = MutableLiveData<List<DesaAdat>>(listOf())
    val banjars = MutableLiveData<List<Banjar>>(listOf())
}